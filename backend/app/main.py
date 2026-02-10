from fastapi import FastAPI, Request, WebSocket, WebSocketDisconnect
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import JSONResponse
from typing import List
import json
from .core.config import get_settings
from .api.routes import auth, qr, logs, reports, admin

settings = get_settings()


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
            except:
                pass


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
