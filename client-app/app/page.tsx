"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { User, KeyRound, Loader2, Dumbbell } from "lucide-react";

export default function LoginPage() {
  const router = useRouter();
  const [identidad, setIdentidad] = useState("");
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");

  const handleLogin = async (e: React.FormEvent) => {
    e.preventDefault();
    setError("");
    setLoading(true);

    try {
      const res = await fetch("/api/client_login.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ identidad: identidad.trim() }),
      });

      const data = await res.json();
      if (data.success) {
        localStorage.setItem("client_token", data.token);
        localStorage.setItem("client_info", JSON.stringify(data.cliente));
        router.push("/dashboard");
      } else {
        setError(data.message || "Identidad no encontrada.");
      }
    } catch (err: any) {
      setError("Error de red: " + (err.message || "No se pudo conectar al servidor."));
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-neutral-950 flex flex-col items-center justify-center p-6">
      <div className="w-full max-w-sm space-y-8">
        <div className="flex flex-col items-center space-y-4">
          <div className="w-20 h-20 bg-amber-500 rounded-2xl flex items-center justify-center shadow-lg shadow-amber-500/20">
            <Dumbbell className="text-black w-10 h-10" />
          </div>
          <h1 className="text-3xl font-bold text-white tracking-tight">Orion Gym</h1>
          <p className="text-neutral-400 text-center">
            Portal exclusivo para miembros. Visualiza tu progreso y rutinas.
          </p>
        </div>

        <form onSubmit={handleLogin} className="space-y-6">
          <div className="space-y-2">
            <label className="text-sm font-medium text-neutral-300">
              Número de Identidad
            </label>
            <div className="relative">
              <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <User className="h-5 w-5 text-neutral-500" />
              </div>
              <input
                type="text"
                required
                className="block w-full pl-10 pr-3 py-3 border border-neutral-800 rounded-xl bg-neutral-900 text-white placeholder-neutral-500 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
                placeholder="Ej. 08011990..."
                value={identidad}
                onChange={(e) => setIdentidad(e.target.value)}
              />
            </div>
          </div>

          {error && (
            <div className="bg-red-500/10 border border-red-500/20 text-red-400 text-sm p-3 rounded-lg flex items-center space-x-2">
              <span>{error}</span>
            </div>
          )}

          <button
            type="submit"
            disabled={loading}
            className="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-black bg-amber-500 hover:bg-amber-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 focus:ring-offset-neutral-950 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {loading ? <Loader2 className="animate-spin w-5 h-5" /> : "Ingresar"}
          </button>
        </form>

        <p className="text-center text-xs text-neutral-600">
          &copy; {new Date().getFullYear()} Orion Gym. Todos los derechos reservados.
        </p>
      </div>
    </div>
  );
}
