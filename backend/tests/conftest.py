import pytest
import asyncio
from datetime import datetime
from sqlalchemy.ext.asyncio import create_async_engine, AsyncSession
from sqlalchemy.orm import sessionmaker
from httpx import AsyncClient, ASGITransport
from app.main import app
from app.core.database import Base, get_db
from app.core.security import get_password_hash
from app.models.models import User, Student, Location

TEST_DATABASE_URL = "sqlite+aiosqlite:///:memory:"


@pytest.fixture(scope="session")
def event_loop():
    loop = asyncio.get_event_loop_policy().new_event_loop()
    yield loop
    loop.close()


@pytest.fixture(scope="function")
async def engine():
    engine = create_async_engine(TEST_DATABASE_URL, echo=False)
    async with engine.begin() as conn:
        await conn.run_sync(Base.metadata.create_all)
    yield engine
    async with engine.begin() as conn:
        await conn.run_sync(Base.metadata.drop_all)
    await engine.dispose()


@pytest.fixture(scope="function")
async def db_session(engine):
    async_session = sessionmaker(engine, class_=AsyncSession, expire_on_commit=False)
    async with async_session() as session:
        yield session


@pytest.fixture(scope="function")
async def client(db_session):
    async def override_get_db():
        yield db_session

    app.dependency_overrides[get_db] = override_get_db
    transport = ASGITransport(app=app)
    async with AsyncClient(transport=transport, base_url="http://test") as ac:
        yield ac
    app.dependency_overrides.clear()


@pytest.fixture
async def test_user(db_session):
    user = User(
        id="test-user-id",
        email="test@example.com",
        password_hash=get_password_hash("TestPass123"),
        role="student",
        is_active=True,
        email_verified=True,
    )
    db_session.add(user)
    await db_session.commit()
    return user


@pytest.fixture
async def test_student(db_session, test_user):
    student = Student(
        id="test-student-id",
        user_id=test_user.id,
        student_id_no="2024-001",
        first_name="Juan",
        last_name="Dela Cruz",
        department="CCS",
        program="BSIT",
        required_hours=500,
        status="active",
    )
    db_session.add(student)
    await db_session.commit()
    return student


@pytest.fixture
async def test_admin(db_session):
    admin = User(
        id="test-admin-id",
        email="admin@example.com",
        password_hash=get_password_hash("AdminPass123"),
        role="admin",
        is_active=True,
        email_verified=True,
    )
    db_session.add(admin)
    await db_session.commit()
    return admin


@pytest.fixture
async def test_location(db_session):
    location = Location(
        id="test-location-id",
        name="Main Gate",
        description="Test location",
        secret_key="test-secret-key-32-characters-long",
        is_active=True,
    )
    db_session.add(location)
    await db_session.commit()
    return location


@pytest.fixture
async def auth_header(client, test_user):
    response = await client.post(
        "/api/auth/login",
        json={"email": "test@example.com", "password": "TestPass123"},
    )
    token = response.json()["data"]["session"]["access_token"]
    return {"Authorization": f"Bearer {token}"}


@pytest.fixture
async def admin_auth_header(client, test_admin):
    response = await client.post(
        "/api/auth/login",
        json={"email": "admin@example.com", "password": "AdminPass123"},
    )
    token = response.json()["data"]["session"]["access_token"]
    return {"Authorization": f"Bearer {token}"}
