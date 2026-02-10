from fastapi import FastAPI, Request, WebSocket, WebSocketDisconnect
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import JSONResponse
from typing import List, Dict
from collections import defaultdict
import json
import time
from .core.config import get_settings
from .api.routes import auth, qr, logs, reports, admin

settings = get_settings()


class RateLimiter:
    def __init__(self, requests_per_minute: int = 60):
        self.requests_per_minute = requests_per_minute
        self.requests: Dict[str, List[float]] = defaultdict(list)

    def is_allowed(self, key: str) -> bool:
        now = time.time()
        window_start = now - 60

        self.requests[key] = [t for t in self.requests[key] if t > window_start]

        if len(self.requests[key]) >= self.requests_per_minute:
            return False

        self.requests[key].append(now)
        return True

    def get_retry_after(self, key: str) -> int:
        if not self.requests[key]:
            return 0
        oldest = min(self.requests[key])
        return max(0, int(60 - (time.time() - oldest)))


rate_limiter = RateLimiter()
auth_rate_limiter = RateLimiter(requests_per_minute=10)


class ConnectionManager:
    def __init__(self):
        self.active_connections: List[WebSocket] = []

    async def connect(self, websocket: WebSocket):
        await websocket.accept()
        self.active_connections.append(websocket)

    def disconnect(self, websocket: WebSocket):
        if websocket in self.active_connections:
            self.active_connections.remove(websocket)

    async def broadcast(self, message: dict):
        for connection in self.active_connections:
            try:
                await connection.send_json(message)
            except Exception:
                self.disconnect(connection)


manager = ConnectionManager()

app = FastAPI(
    title=settings.APP_NAME,
    version=settings.APP_VERSION,
    docs_url="/api/docs",
    redoc_url="/api/redoc",
    openapi_url="/api/openapi.json",
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=settings.CORS_ORIGINS,
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)


@app.middleware("http")
async def rate_limit_middleware(request: Request, call_next):
    if request.url.path.startswith("/api/auth"):
        client_ip = request.client.host if request.client else "unknown"
        key = f"auth:{client_ip}"

        if not auth_rate_limiter.is_allowed(key):
            retry_after = auth_rate_limiter.get_retry_after(key)
            return JSONResponse(
                status_code=429,
                content={"error": "Too many requests", "retry_after": retry_after},
                headers={"Retry-After": str(retry_after)},
            )

    elif request.url.path.startswith("/api/qr/validate"):
        client_ip = request.client.host if request.client else "unknown"
        key = f"qr:{client_ip}"

        if not rate_limiter.is_allowed(key):
            retry_after = rate_limiter.get_retry_after(key)
            return JSONResponse(
                status_code=429,
                content={"error": "Too many requests", "retry_after": retry_after},
                headers={"Retry-After": str(retry_after)},
            )

    return await call_next(request)


@app.exception_handler(Exception)
async def global_exception_handler(request: Request, exc: Exception):
    return JSONResponse(
        status_code=500,
        content={
            "error": "Internal server error",
            "detail": str(exc) if settings.DEBUG else None,
        },
    )


app.include_router(auth, prefix="/api/auth", tags=["Authentication"])
app.include_router(qr, prefix="/api/qr", tags=["QR Code"])
app.include_router(logs, prefix="/api/logs", tags=["Time Logs"])
app.include_router(reports, prefix="/api/reports", tags=["Reports"])
app.include_router(admin, prefix="/api/admin", tags=["Admin"])


@app.get("/")
async def root():
    return {"name": settings.APP_NAME, "version": settings.APP_VERSION}


@app.get("/health")
async def health_check():
    return {
        "status": "healthy",
        "websocket_connections": len(manager.active_connections),
    }


@app.websocket("/ws/attendance")
async def websocket_attendance(websocket: WebSocket):
    await manager.connect(websocket)
    try:
        while True:
            data = await websocket.receive_text()
            try:
                message = json.loads(data)
                if message.get("type") == "ping":
                    await websocket.send_json({"type": "pong"})
            except json.JSONDecodeError:
                pass
    except WebSocketDisconnect:
        manager.disconnect(websocket)


async def broadcast_clock_event(
    student_name: str, log_type: str, log_category: str, timestamp: str
):
    await manager.broadcast(
        {
            "type": "clock_event",
            "data": {
                "student_name": student_name,
                "log_type": log_type,
                "log_category": log_category,
                "timestamp": timestamp,
            },
        }
    )
