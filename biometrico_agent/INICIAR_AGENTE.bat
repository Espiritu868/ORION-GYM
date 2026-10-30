@echo off
title Orion Gym - Servicio Biometrico
color 0A
cd /d "%~dp0"
python agent_py.py
if %ERRORLEVEL% NEQ 0 (
    py agent_py.py
)
pause
