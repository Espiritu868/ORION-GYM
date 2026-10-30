"use client";

import { useEffect, useState } from "react";
import { Dumbbell, Calendar as CalendarIcon, ChevronRight } from "lucide-react";
import { motion } from "framer-motion";
import Link from "next/link";

export default function RutinasAvanzadasPage() {
  const [rutinas, setRutinas] = useState<any>({});
  const [loading, setLoading] = useState(true);
  const [activeWorkoutDay, setActiveWorkoutDay] = useState<number | null>(null);

  const dias = [
    { num: 1, name: 'Lunes' }, { num: 2, name: 'Martes' }, { num: 3, name: 'Miércoles' },
    { num: 4, name: 'Jueves' }, { num: 5, name: 'Viernes' }, { num: 6, name: 'Sábado' }, { num: 7, name: 'Domingo' }
  ];

  useEffect(() => {
    // Check for active workouts first
    let foundActive = null;
    for (let i = 1; i <= 7; i++) {
      if (localStorage.getItem(`orion_workout_active_${i}`)) {
        foundActive = i;
        break;
      }
    }
    
    if (foundActive) {
      setActiveWorkoutDay(foundActive);
      setLoading(false);
      return;
    }

    const info = localStorage.getItem("client_info");
    if (info) {
      const parsed = JSON.parse(info);
      fetchRoutines(parsed.id_cliente);
    } else {
      setLoading(false);
    }
  }, []);

  const fetchRoutines = async (id_cliente: string) => {
    try {
      const res = await fetch(`/api/client_advanced_routines.php?id_cliente=${id_cliente}`);
      const data = await res.json();
      if (data.success) {
        setRutinas(data.rutinas);
      }
    } catch (error) {
      console.error(error);
    } finally {
      setLoading(false);
    }
  };

  const todayIndex = new Date().getDay() === 0 ? 7 : new Date().getDay();

  const handleCancelWorkout = () => {
    if (confirm("¿Estás seguro de cancelar tu entrenamiento actual? Se perderá todo tu progreso de hoy.")) {
      if (activeWorkoutDay) {
        localStorage.removeItem(`orion_workout_active_${activeWorkoutDay}`);
      }
      setActiveWorkoutDay(null);
      setLoading(true);
      const info = localStorage.getItem("client_info");
      if (info) {
        fetchRoutines(JSON.parse(info).id_cliente);
      }
    }
  };

  if (loading) return <div className="text-center text-neutral-400 mt-10">Cargando rutinas...</div>;

  if (activeWorkoutDay !== null) {
    const dayName = dias.find(d => d.num === activeWorkoutDay)?.name || "Hoy";
    return (
      <div className="flex flex-col items-center justify-center min-h-[60vh] text-center px-4">
        <div className="w-20 h-20 bg-blue-500/20 rounded-full flex items-center justify-center mb-6">
          <Dumbbell className="w-10 h-10 text-blue-500 animate-pulse" />
        </div>
        <h2 className="text-2xl font-bold text-white mb-2">¡Entrenamiento en Curso!</h2>
        <p className="text-neutral-400 mb-8 max-w-sm">
          Tienes una sesión activa de la rutina del <strong>{dayName}</strong>. Por favor terminala o cancélala antes de iniciar otro día.
        </p>
        
        <div className="w-full space-y-4 max-w-sm">
          <Link 
            href={`/dashboard/entreno/${activeWorkoutDay}`} 
            className="block w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-4 rounded-2xl transition-colors shadow-lg shadow-blue-500/20"
          >
            Continuar Entrenamiento
          </Link>
          
          <button 
            onClick={handleCancelWorkout}
            className="block w-full border border-red-500/30 text-red-500 hover:bg-red-500/10 font-bold py-4 rounded-2xl transition-colors"
          >
            Cancelar Entrenamiento
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center space-x-3 mb-6">
        <div className="bg-green-500/10 p-3 rounded-xl">
          <CalendarIcon className="w-6 h-6 text-green-500" />
        </div>
        <div>
          <h1 className="text-2xl font-bold text-white">Mi Plan de Entrenamiento</h1>
          <p className="text-sm text-neutral-400">Diseñado especialmente para ti</p>
        </div>
      </div>

      <div className="space-y-4">
        {dias.map((dia, idx) => {
          const isToday = dia.num === todayIndex;
          const ejercicios = rutinas[dia.num] || [];
          const hasRoutine = ejercicios.length > 0;

          return (
            <motion.div 
              initial={{ opacity: 0, y: 10 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: idx * 0.1 }}
              key={dia.num} 
              className={`border rounded-2xl p-5 relative overflow-hidden transition-all ${
                isToday 
                  ? "bg-neutral-900 border-amber-500 shadow-lg shadow-amber-500/10" 
                  : "bg-neutral-900/50 border-neutral-800"
              }`}
            >
              {isToday && (
                <div className="absolute top-0 right-0 bg-amber-500 text-black text-[10px] font-bold px-3 py-1 rounded-bl-lg">
                  HOY
                </div>
              )}
              
              <div className="flex justify-between items-center mb-3">
                <div className="flex items-center space-x-3">
                  <div className={`p-2 rounded-lg ${isToday ? "bg-amber-500/10" : "bg-neutral-800"}`}>
                    <Dumbbell className={`w-5 h-5 ${isToday ? "text-amber-500" : "text-neutral-500"}`} />
                  </div>
                  <h3 className={`font-bold text-lg ${isToday ? "text-amber-500" : "text-white"}`}>{dia.name}</h3>
                </div>
                {hasRoutine && (
                  <Link href={`/dashboard/entreno/${dia.num}`} className="bg-amber-500 text-black text-xs font-bold px-4 py-2 rounded-lg hover:bg-amber-400 transition-colors">
                    Iniciar
                  </Link>
                )}
              </div>

              {hasRoutine ? (
                <div className="space-y-2 mt-4">
                  {ejercicios.map((ej: any, i: number) => (
                    <div key={i} className="flex items-center justify-between text-sm border-b border-neutral-800 pb-2 last:border-0">
                      <span className="text-neutral-300 font-medium">{ej.series.length}x {ej.nombre}</span>
                      <span className="text-neutral-500">{ej.grupo_muscular}</span>
                    </div>
                  ))}
                </div>
              ) : (
                <p className="text-neutral-600 text-sm mt-2 italic">Día de descanso.</p>
              )}
            </motion.div>
          );
        })}
      </div>
    </div>
  );
}
