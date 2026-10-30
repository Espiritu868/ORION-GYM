import json
import time
import urllib.request
import urllib.error
from zk import ZK, const

def load_config():
    with open('config.json', 'r') as f:
        return json.load(f)

config = load_config()
zk_ip = config.get('zk_ip', '192.168.55.249')
zk_port = int(config.get('zk_port', 4370))
api_url = config.get('api_url', 'http://localhost/Orion_gym/api/biometrico.php')
api_token = config.get('api_token', 'ORION_GYM_ZK_SECURE_TOKEN_2026')
comm_key = int(config.get('comm_key', 0))

print("====================================================")
print("   ORION GYM - AGENTE BIOMETRICO ZKTECO (PYTHON)    ")
print("====================================================")
print(f"IP ZKTeco:  {zk_ip}:{zk_port}")
print(f"URL API:    {api_url}")
print("----------------------------------------------------")

def send_to_web(user_id, timestamp):
    url = f"{api_url}?action=log_attendance"
    payload = json.dumps({
        "id_cliente": int(user_id),
        "timestamp": str(timestamp)
    }).encode('utf-8')
    
    req = urllib.request.Request(
        url,
        data=payload,
        headers={
            'Content-Type': 'application/json',
            'Authorization': f'Bearer {api_token}'
        }
    )
    try:
        with urllib.request.urlopen(req, timeout=5) as response:
            res_data = response.read().decode('utf-8')
            res = json.loads(res_data)
            if res.get('grant_access'):
                print(f"\n>>> [ACCESO CONCEDIDO] Miembro: {res.get('cliente')} | Plan: {res.get('plan')} | Vence: {res.get('vence')}\n")
            else:
                print(f"\n>>> [ACCESO DENEGADO] Miembro: {res.get('cliente')} | Motivo: {res.get('message')}\n")
    except Exception as e:
        print(f"[ERROR WEB SYNC] {e}")

zk = ZK(zk_ip, port=zk_port, timeout=5, password=comm_key, force_udp=False)

print(f"[INFO] Conectando con ZKTeco en {zk_ip}...")
conn = None
try:
    conn = zk.connect()
    print("[EXITO] ¡Conectado al ZKTeco!")
    print(f"Firmware: {conn.get_firmware_version()}")
    print("----------------------------------------------------")
    print("[LISTO] Escuchando huellas en tiempo real...")
    print(">> Coloque su dedo en el sensor ZKTeco <<\n")

    for attendance in conn.live_capture():
        if attendance is not None:
            user_id = attendance.user_id
            ts = attendance.timestamp
            print(f"[{time.strftime('%H:%M:%S')}] ¡HUELLA CAPTURADA! Miembro ID: #{user_id} | Hora: {ts}")
            send_to_web(user_id, ts)

except KeyboardInterrupt:
    print("\nDeteniendo servicio...")
except Exception as e:
    print(f"\n[ERROR] Ocurrio un error: {e}")
finally:
    if conn:
        conn.disconnect()
        print("Desconectado del ZKTeco.")

