import pytest


class TestAuthEndpoints:
    @pytest.mark.asyncio
    async def test_register_student(self, client):
        response = await client.post(
            "/api/auth/register/student",
            json={
                "email": "newstudent@example.com",
                "password": "TestPass123",
                "first_name": "Juan",
                "last_name": "Dela Cruz",
                "student_id_no": "2024-002",
                "department": "CCS",
                "program": "BSIT",
                "contact_no": "09171234567",
            },
        )
        assert response.status_code == 200
        data = response.json()
        assert data["success"] is True
        assert "user_id" in data["data"]

    @pytest.mark.asyncio
    async def test_register_duplicate_email(self, client, test_user):
        response = await client.post(
            "/api/auth/register/student",
            json={
                "email": "test@example.com",
                "password": "TestPass123",
                "first_name": "Juan",
                "last_name": "Dela Cruz",
                "student_id_no": "2024-003",
                "department": "CCS",
                "program": "BSIT",
            },
        )
        assert response.status_code == 409

    @pytest.mark.asyncio
    async def test_login_success(self, client, test_user):
        response = await client.post(
            "/api/auth/login",
            json={"email": "test@example.com", "password": "TestPass123"},
        )
        assert response.status_code == 200
        data = response.json()
        assert data["success"] is True
        assert "access_token" in data["data"]["session"]

    @pytest.mark.asyncio
    async def test_login_wrong_password(self, client, test_user):
        response = await client.post(
            "/api/auth/login",
            json={"email": "test@example.com", "password": "WrongPassword"},
        )
        assert response.status_code == 401

    @pytest.mark.asyncio
    async def test_login_nonexistent_user(self, client):
        response = await client.post(
            "/api/auth/login",
            json={"email": "nonexistent@example.com", "password": "TestPass123"},
        )
        assert response.status_code == 401

    @pytest.mark.asyncio
    async def test_get_me(self, client, auth_header):
        response = await client.get("/api/auth/me", headers=auth_header)
        assert response.status_code == 200
        data = response.json()
        assert data["success"] is True
        assert data["data"]["email"] == "test@example.com"

    @pytest.mark.asyncio
    async def test_get_me_unauthorized(self, client):
        response = await client.get("/api/auth/me")
        assert response.status_code == 403

    @pytest.mark.asyncio
    async def test_logout(self, client, auth_header):
        response = await client.post("/api/auth/logout", headers=auth_header)
        assert response.status_code == 200
