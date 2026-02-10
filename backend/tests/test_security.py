import pytest
from app.core.security import (
    get_password_hash,
    verify_password,
    create_access_token,
    decode_token,
)
from app.utils.hash import hash_token


class TestSecurity:
    def test_password_hashing(self):
        password = "TestPassword123"
        hashed = get_password_hash(password)
        assert hashed != password
        assert verify_password(password, hashed)

    def test_password_verification_wrong_password(self):
        password = "TestPassword123"
        hashed = get_password_hash(password)
        assert not verify_password("WrongPassword", hashed)

    def test_create_access_token(self):
        payload = {"sub": "user123", "role": "student"}
        token = create_access_token(payload)
        assert token is not None
        assert isinstance(token, str)

    def test_decode_access_token(self):
        payload = {"sub": "user123", "role": "student"}
        token = create_access_token(payload)
        decoded = decode_token(token)
        assert decoded is not None
        assert decoded["sub"] == "user123"
        assert decoded["type"] == "access"

    def test_decode_invalid_token(self):
        decoded = decode_token("invalid-token")
        assert decoded is None

    def test_hash_token(self):
        token = "my-secret-token"
        hashed = hash_token(token)
        assert hashed != token
        assert len(hashed) == 64
