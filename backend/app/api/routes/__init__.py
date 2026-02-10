from .auth import router as auth_router
from .qr import router as qr_router
from .logs import router as logs_router
from .reports import router as reports_router
from .admin import router as admin_router

auth = auth_router
qr = qr_router
logs = logs_router
reports = reports_router
admin = admin_router
