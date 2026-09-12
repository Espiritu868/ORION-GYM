"use client";

import { useEffect, useState } from "react";
import { Dumbbell, Trophy, Activity, ArrowRight, Calendar } from "lucide-react";
import Link from "next/link";
import { motion } from "framer-motion";

export default function DashboardPage() {
  const [clientInfo, setClientInfo] = useState<any>(null);
  const [todayRoutine, setTodayRoutine] = useState<string>("Cargando...");

  useEffect(() => {
    const info = localStorage.getItem("client_info");
    if (info) {
      const parsed = JSON.parse(info);
      setClientInfo(parsed);
      fetchRoutine(parsed.id_cliente);
    }
  }, []);

  const fetchRoutine = async (id_cliente: string) => {
    try {
      const res = await fetch(`/api/client_advanced_routines.php?id_cliente=${id_cliente}`);
      const data = await res.json();
      if (data.success) {
        const today = new Date().getDay(); 
        const adjustedDay = today === 0 ? 7 : today; 
        const ejercicios = data.rutinas[adjustedDay];
        
        if (ejercicios && ejercicios.length > 0) {
           const names = ejercicios.map((ej: any) => ej.nombre).join(', ');
           setTodayRoutine(`Hoy toca:\n${names}`);
        } else {
           setTodayRoutine("¡Día de Descanso! Recupera energías.");
        }
      }
    } catch (error) {
      setTodayRoutine("Error cargando rutina.");
    }
  };

  if (!clientInfo) return null;

  return (
    <div className="space-y-6">
      {/* Welcome Card */}
      <motion.div 
        initial={{ scale: 0.95, opacity: 0 }} 
        animate={{ scale: 1, opacity: 1 }} 
        className="bg-gradient-to-br from-amber-500 to-amber-600 rounded-3xl p-6 shadow-xl shadow-amber-500/10 relative overflow-hidden"
      >
        <div className="absolute top-0 right-0 p-4 opacity-20">
          <Trophy className="w-24 h-24 text-black" />
        </div>
        <div className="relative z-10">
          <h1 className="text-3xl font-black text-black mb-1">¡Hola, {clientInfo.nombre_completo.split(' ')[0]}!</h1>
          <p className="text-amber-950 font-medium mb-6">Listo para romper tus límites hoy.</p>
          
          <div className="inline-flex items-center space-x-2 bg-black/20 backdrop-blur-md rounded-xl px-4 py-2 text-black text-sm font-bold">
            <Activity className="w-4 h-4" />
            <span>Miembro desde {new Date(clientInfo.fecha_ingreso).getFullYear()}</span>
          </div>
        </div>
      </motion.div>

      {/* Today's Routine */}
      <div className="space-y-3">
        <div className="flex items-center justify-between">
          <h3 className="text-lg font-bold text-white">Tu Entrenamiento de Hoy</h3>
          <Link href="/dashboard/rutinas" className="text-sm text-amber-500 font-medium flex items-center">
            Ver todas <ArrowRight className="w-4 h-4 ml-1" />
          </Link>
        </div>
        
        <div className="bg-neutral-900 border border-neutral-800 rounded-2xl p-5 relative overflow-hidden">
          <div className="absolute top-0 left-0 w-1 h-full bg-amber-500"></div>
          <div className="flex items-start space-x-4">
            <div className="bg-neutral-800 p-3 rounded-xl">
              <Dumbbell className="w-6 h-6 text-amber-500" />
            </div>
            <div>
              <h4 className="text-white font-bold mb-1">Enfoque del Día</h4>
              <p className="text-neutral-400 text-sm whitespace-pre-wrap">{todayRoutine}</p>
            </div>
          </div>
        </div>
      </div>

      {/* Quick Actions */}
      <div className="grid grid-cols-2 gap-4">
        <Link href="/dashboard/progreso" className="bg-neutral-900 border border-neutral-800 rounded-2xl p-4 flex flex-col justify-center items-center text-center hover:bg-neutral-800 transition-colors">
          <Activity className="w-8 h-8 text-blue-500 mb-2" />
          <span className="font-bold text-white text-sm">Ver Progreso</span>
        </Link>
        <Link href="/dashboard/rutinas" className="bg-neutral-900 border border-neutral-800 rounded-2xl p-4 flex flex-col justify-center items-center text-center hover:bg-neutral-800 transition-colors">
          <Calendar className="w-8 h-8 text-green-500 mb-2" />
          <span className="font-bold text-white text-sm">Plan Semanal</span>
        </Link>
      </div>
    </div>
  );
}
