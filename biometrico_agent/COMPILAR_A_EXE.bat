@echo off
title Orion Gym - Compilador de Agente
color 0B
echo ====================================================
echo      COMPILANDO AGENTE A EJECUTABLE (.EXE)
echo ====================================================
echo.
echo 1. Instalando PyInstaller...
pip install pyinstaller

echo.
echo 2. Empaquetando agente en un solo archivo .exe...
pyinstaller --noconfirm --onedir --console --name "OrionGym_Biometrico" --add-data "config.json;." agent_py.py

echo.
echo ====================================================
echo  COPIANDO ARCHIVOS A LA CARPETA DISTRIBUCION...
echo ====================================================
if exist "dist\OrionGym_Biometrico\OrionGym_Biometrico.exe" (
    copy /Y "config.json" "dist\OrionGym_Biometrico\config.json"
    echo.
    echo ¡COMPILACION EXITOSA!
    echo Tu ejecutable listo para el cliente esta en:
    echo biometrico_agent\dist\OrionGym_Biometrico\OrionGym_Biometrico.exe
)

pause

