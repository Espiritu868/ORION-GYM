"use client";

import { useEffect, useState } from "react";
import { useParams, useRouter } from "next/navigation";
import { Dumbbell, Clock, CheckCircle2, Circle, ArrowLeft, Loader2, Gauge, X, Plus, Search, Trash2, Timer } from "lucide-react";
import { motion, AnimatePresence } from "framer-motion";

export default function EntrenoPage() {
  const params = useParams();
  const router = useRouter();
  const dia = parseInt(params.dia as string);

  const STORAGE_KEY = `orion_workout_active_${dia}`;

  const [clienteId, setClienteId] = useState<number>(0);
  const [ejercicios, setEjercicios] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  
  // Timer States
  const [workoutStartTime, setWorkoutStartTime] = useState<number>(0);
  const [restEndTime, setRestEndTime] = useState<number>(0);
  const [timeStr, setTimeStr] = useState("00:00");
  const [restTimeStr, setRestTimeStr] = useState("00:00");
  const [timeElapsed, setTimeElapsed] = useState(0); // For API saving
  const [isResting, setIsResting] = useState<boolean>(false);

  // Catalog State
  const [catalog, setCatalog] = useState<any[]>([]);
  const [showCatalog, setShowCatalog] = useState(false);
  const [searchTerm, setSearchTerm] = useState("");

  // RPE Drawer State
  const [rpeDrawerOpen, setRpeDrawerOpen] = useState(false);
  const [activeSet, setActiveSet] = useState<{exIdx: number, setIdx: number} | null>(null);
  const [tempRpe, setTempRpe] = useState<number | null>(null);

  // Set Type Drawer State
  const [typeDrawerOpen, setTypeDrawerOpen] = useState(false);
  const [activeTypeSet, setActiveTypeSet] = useState<{exIdx: number, setIdx: number} | null>(null);

  // Initial Data Load (Checks Cache First)
  useEffect(() => {
    // Request notification permissions once
    if (typeof Notification !== 'undefined' && Notification.permission === 'default') {
      Notification.requestPermission();
    }

    const info = localStorage.getItem("client_info");
    if (info) {
      const parsed = JSON.parse(info);
      setClienteId(parsed.id_cliente);
      
      fetchCatalog();

      const cached = localStorage.getItem(STORAGE_KEY);
      if (cached) {
        try {
          const parsedCache = JSON.parse(cached);
          setEjercicios(parsedCache.ejercicios || []);
          setWorkoutStartTime(parsedCache.workoutStartTime || Date.now());
          setRestEndTime(parsedCache.restEndTime || 0);
          setLoading(false);
        } catch (e) {
          fetchRoutine(parsed.id_cliente);
        }
      } else {
        fetchRoutine(parsed.id_cliente);
      }
    } else {
      router.push("/");
    }
  }, []);

  // Sync to Cache on changes
  useEffect(() => {
    if (!loading && workoutStartTime > 0) {
      localStorage.setItem(STORAGE_KEY, JSON.stringify({
        ejercicios,
        workoutStartTime,
        restEndTime
      }));
    }
  }, [ejercicios, workoutStartTime, restEndTime, loading]);

  // Audio Beep Generator
  const playBeep = () => {
    try {
      const AudioContext = window.AudioContext || (window as any).webkitAudioContext;
      if (!AudioContext) return;
      const ctx = new AudioContext();
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();
      
      osc.connect(gain);
      gain.connect(ctx.destination);
      
      osc.type = "sine";
      osc.frequency.setValueAtTime(800, ctx.currentTime);
      gain.gain.setValueAtTime(1, ctx.currentTime);
      gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.5);
      
      osc.start();
      osc.stop(ctx.currentTime + 0.5);
    } catch(e) {}
  };

  // Global Clock & Rest Timer
  useEffect(() => {
    if (loading || workoutStartTime === 0) return;

    const interval = setInterval(() => {
      const now = Date.now();
      
      // Workout Elapsed Time
      const elapsedSecs = Math.max(0, Math.floor((now - workoutStartTime) / 1000));
      setTimeStr(formatTime(elapsedSecs));
      setTimeElapsed(elapsedSecs);

      // Rest Time
      if (restEndTime > now) {
        const remaining = Math.max(0, Math.ceil((restEndTime - now) / 1000));
        setRestTimeStr(formatTime(remaining));
        setIsResting(true);
      } else if (isResting) {
        setIsResting(false);
        setRestEndTime(0);
        
        playBeep();
        if (typeof Notification !== 'undefined' && Notification.permission === 'granted') {
          new Notification("¡Descanso Terminado!", {
            body: "Es hora de tu siguiente serie. ¡A darle!",
            requireInteraction: true
          });
        }
      }
    }, 1000);

    return () => clearInterval(interval);
  }, [loading, workoutStartTime, restEndTime, isResting]);

  const fetchRoutine = async (id: number) => {
    try {
      const res = await fetch(`/api/client_advanced_routines.php?id_cliente=${id}`);
      const data = await res.json();
      if (data.success && data.rutinas[dia]) {
        const ejs = data.rutinas[dia].map((ej: any) => ({
          ...ej,
          series: ej.series.map((s: any) => ({
            ...s,
            lbs_real: s.lbs_obj,
            reps_real: s.reps_obj ? s.reps_obj.toString().split('-')[0] : '',
            rpe_real: '',
            tipo_serie: s.tipo_serie || 'N',
            completada: false
          }))
        }));
        
        const now = Date.now();
        setEjercicios(ejs);
        setWorkoutStartTime(now);
        setRestEndTime(0);
        
        // Initial cache save
        localStorage.setItem(STORAGE_KEY, JSON.stringify({
          ejercicios: ejs,
          workoutStartTime: now,
          restEndTime: 0
        }));
      } else {
        setEjercicios([]);
        setWorkoutStartTime(Date.now());
      }
    } catch (error) {
      console.error(error);
    } finally {
      setLoading(false);
    }
  };

  const fetchCatalog = async () => {
    try {
      const res = await fetch(`/api/client_catalog.php`);
      const data = await res.json();
      if (data.success) {
        setCatalog(data.data);
      }
    } catch (error) {
      console.error(error);
    }
  };

  const handleSetChange = (exIdx: number, setIdx: number, field: string, val: string | number) => {
    const newEjs = [...ejercicios];
    newEjs[exIdx].series[setIdx][field] = val;
    setEjercicios(newEjs);
  };

  const toggleSetComplete = (exIdx: number, setIdx: number) => {
    const newEjs = [...ejercicios];
    const isNowComplete = !newEjs[exIdx].series[setIdx].completada;
    newEjs[exIdx].series[setIdx].completada = isNowComplete;
    setEjercicios(newEjs);

    if (isNowComplete) {
      const desc = parseInt(newEjs[exIdx].descanso_segundos) || 0;
      if (desc > 0) {
        const newRestEnd = Date.now() + (desc * 1000);
        setRestEndTime(newRestEnd);
        setIsResting(true);
      }
    }
  };

  const addSet = (exIdx: number) => {
    const newEjs = [...ejercicios];
    const series = newEjs[exIdx].series;
    const lastSet = series.length > 0 ? series[series.length - 1] : null;
    const nextNum = series.length + 1; 

    series.push({
      numero_serie: nextNum,
      lbs_real: lastSet ? lastSet.lbs_real : '',
      reps_real: lastSet ? lastSet.reps_real : '',
      rpe_real: '',
      tipo_serie: 'N',
      completada: false,
      anterior: null
    });
    setEjercicios(newEjs);
  };

  const removeSet = (exIdx: number, setIdx: number) => {
    const newEjs = [...ejercicios];
    newEjs[exIdx].series.splice(setIdx, 1);
    setEjercicios(newEjs);
  };

  const addExercise = (ej: any) => {
    const newEjs = [...ejercicios];
    newEjs.push({
      id_ejercicio: ej.id_ejercicio,
      nombre: ej.nombre,
      descanso_segundos: 120,
      series: [{
        numero_serie: 1,
        lbs_real: '',
        reps_real: '',
        rpe_real: '',
        tipo_serie: 'N',
        completada: false,
        anterior: null
      }]
    });
    setEjercicios(newEjs);
    setShowCatalog(false);
  };

  const removeExercise = (exIdx: number) => {
    if(confirm("¿Seguro que deseas eliminar este ejercicio de la rutina de hoy?")) {
      const newEjs = [...ejercicios];
      newEjs.splice(exIdx, 1);
      setEjercicios(newEjs);
    }
  };

  const finishWorkout = async () => {
    setSaving(true);
    try {
      const payloadEjercicios = ejercicios.map(ej => ({
        ...ej,
        series: ej.series.map((s: any, idx: number) => ({
          ...s,
          numero_serie: idx + 1
        }))
      }));

      const res = await fetch(`/api/client_save_workout.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          id_cliente: clienteId,
          dia_semana: dia,
          duracion_segundos: timeElapsed,
          ejercicios_log: payloadEjercicios
        })
      });
      const data = await res.json();
      if (data.success) {
        localStorage.removeItem(STORAGE_KEY);
        alert("¡Entrenamiento finalizado con éxito!");
        router.push("/dashboard");
      } else {
        alert("Error al guardar: " + data.message);
      }
    } catch (error) {
      alert("Error de conexión");
    } finally {
      setSaving(false);
    }
  };

  const formatTime = (secs: number) => {
    const m = Math.floor(secs / 60).toString().padStart(2, '0');
    const s = (secs % 60).toString().padStart(2, '0');
    return `${m}:${s}`;
  };

  const handleCancelWorkout = () => {
    if (confirm("¿Estás seguro de cancelar el entrenamiento? Se perderá todo tu progreso de hoy.")) {
      localStorage.removeItem(STORAGE_KEY);
      router.push("/dashboard");
    }
  };

  const getSetTypeBadge = (tipo: string, num: string | number) => {
    switch (tipo) {
      case 'W': return <div className="w-full text-center text-amber-500 font-bold text-sm bg-amber-500/10 rounded py-0.5 shadow-sm border border-amber-500/20">W</div>;
      case 'D': return <div className="w-full text-center text-indigo-400 font-bold text-sm bg-indigo-500/10 rounded py-0.5 shadow-sm border border-indigo-400/20">D</div>;
      case 'F': return <div className="w-full text-center text-red-500 font-bold text-sm bg-red-500/10 rounded py-0.5 shadow-sm border border-red-500/20">F</div>;
      default: return <div className="w-full text-center text-white font-bold text-sm bg-neutral-800 rounded py-0.5">{num}</div>;
    }
  };

  // Drawers functions
  const openRpeDrawer = (exIdx: number, setIdx: number) => {
    setActiveSet({ exIdx, setIdx });
    setTempRpe(parseFloat(ejercicios[exIdx].series[setIdx].rpe_real) || null);
    setRpeDrawerOpen(true);
  };
  const closeRpeDrawer = () => {
    setRpeDrawerOpen(false);
    setTimeout(() => { setActiveSet(null); setTempRpe(null); }, 300);
  };
  const saveRpe = () => {
    if (activeSet && tempRpe !== null) {
      handleSetChange(activeSet.exIdx, activeSet.setIdx, 'rpe_real', tempRpe);
    }
    closeRpeDrawer();
  };
  const rpeValues = [6, 6.5, 7, 7.5, 8, 8.5, 9, 9.5, 10];

  const openTypeDrawer = (exIdx: number, setIdx: number) => {
    setActiveTypeSet({ exIdx, setIdx });
    setTypeDrawerOpen(true);
  };
  const closeTypeDrawer = () => {
    setTypeDrawerOpen(false);
    setTimeout(() => setActiveTypeSet(null), 300);
  };
  const saveType = (type: string) => {
    if (activeTypeSet) {
      handleSetChange(activeTypeSet.exIdx, activeTypeSet.setIdx, 'tipo_serie', type);
    }
    closeTypeDrawer();
  };
  const closeRestTimer = () => {
    setRestEndTime(0);
    setIsResting(false);
  };


  const filteredCatalog = catalog.filter(ej => ej.nombre.toLowerCase().includes(searchTerm.toLowerCase()) || ej.grupo_muscular.toLowerCase().includes(searchTerm.toLowerCase()));

  if (loading) return <div className="text-center text-neutral-400 mt-20"><Loader2 className="w-8 h-8 animate-spin mx-auto mb-4" /> Preparando pesas...</div>;

  return (
    <div className="bg-black min-h-screen pb-40 -mx-4 -mt-6 px-4 pt-6 relative">
      {/* Top Header */}
      <div className="flex items-center justify-between mb-8 sticky top-0 bg-black/90 backdrop-blur-md py-4 z-10 border-b border-neutral-900">
        <button onClick={() => router.push('/dashboard/rutinas')} className="text-neutral-400">
          <ArrowLeft className="w-6 h-6" />
        </button>
        <div className="flex flex-col items-center">
          <span className="text-white font-bold text-lg">Entrenamiento</span>
          <div className="flex items-center text-blue-500 text-sm font-medium">
            <Clock className="w-4 h-4 mr-1" /> {timeStr}
          </div>
        </div>
        <div className="flex items-center space-x-2">
          <button onClick={handleCancelWorkout} className="text-neutral-500 hover:text-red-500 p-2 transition-colors">
            <X className="w-6 h-6" />
          </button>
          <button onClick={finishWorkout} disabled={saving} className="bg-blue-600 hover:bg-blue-500 text-white px-4 py-1.5 rounded-full text-sm font-bold transition-colors shadow-lg shadow-blue-500/20">
            {saving ? '...' : 'Terminar'}
          </button>
        </div>
      </div>

      <div className="space-y-8">
        {ejercicios.map((ej, exIdx) => {
          let workingSetCount = 0;
          return (
            <div key={exIdx} className="space-y-4">
              <div className="flex items-center justify-between">
                <h2 className="text-blue-400 font-bold text-lg flex items-center">
                  <Dumbbell className="w-5 h-5 mr-2" /> {ej.nombre}
                </h2>
                <button onClick={() => removeExercise(exIdx)} className="text-neutral-600 hover:text-red-500 transition-colors p-2">
                  <X className="w-5 h-5" />
                </button>
              </div>
              
              <div className="text-blue-500 text-sm flex items-center mb-2">
                <Clock className="w-4 h-4 mr-1" /> Descanso: {ej.descanso_segundos}s
              </div>

              <div className="space-y-2">
                {/* Header Row */}
                <div className="grid grid-cols-[35px_1fr_40px_40px_40px_35px_30px] gap-2 text-[9px] font-bold text-neutral-500 text-center tracking-wider px-1">
                  <div className="text-center">SET</div>
                  <div className="text-left">ANTERIOR</div>
                  <div>LBS</div>
                  <div>REPS</div>
                  <div>RPE</div>
                  <div><CheckCircle2 className="w-3 h-3 inline mx-auto" /></div>
                  <div></div>
                </div>

                {/* Set Rows */}
                {ej.series.map((set: any, setIdx: number) => {
                  let displayNum: string | number = '';
                  if (set.tipo_serie === 'N' || set.tipo_serie === 'F') {
                    workingSetCount++;
                    displayNum = workingSetCount;
                  }
                  
                  return (
                    <div key={setIdx} className={`grid grid-cols-[35px_1fr_40px_40px_40px_35px_30px] gap-2 items-center px-1 py-2 rounded-xl transition-colors ${set.completada ? 'bg-green-900/30' : 'bg-neutral-900/60'}`}>
                      
                      <button onClick={() => openTypeDrawer(exIdx, setIdx)} className="w-full h-full flex items-center justify-center transition-transform active:scale-95">
                        {getSetTypeBadge(set.tipo_serie, displayNum)}
                      </button>
                      
                      <div className="text-left text-neutral-400 text-[10px] leading-tight overflow-hidden">
                        {set.anterior ? (
                          <>{set.anterior.lbs}lbs x {set.anterior.reps}</>
                        ) : (
                          <span className="font-medium text-neutral-500 truncate block">{set.reps_obj ? `Obj: ${set.reps_obj}` : '-'}</span>
                        )}
                      </div>
                      
                      <input 
                        type="number" 
                        value={set.lbs_real} 
                        onChange={(e) => handleSetChange(exIdx, setIdx, 'lbs_real', e.target.value)}
                        className="w-full bg-neutral-800 text-white font-bold text-center rounded py-1.5 border border-transparent focus:border-blue-500 outline-none text-xs px-0 shadow-inner"
                      />
                      <input 
                        type="number" 
                        value={set.reps_real} 
                        onChange={(e) => handleSetChange(exIdx, setIdx, 'reps_real', e.target.value)}
                        className="w-full bg-neutral-800 text-white font-bold text-center rounded py-1.5 border border-transparent focus:border-blue-500 outline-none text-xs px-0 shadow-inner"
                      />
                      
                      <button 
                        onClick={() => openRpeDrawer(exIdx, setIdx)}
                        className="w-full h-full bg-neutral-800 text-white font-bold text-center rounded border border-transparent flex items-center justify-center text-xs shadow-inner"
                      >
                        {set.rpe_real ? set.rpe_real : <Gauge className="w-3 h-3 text-neutral-500" />}
                      </button>
                      
                      <button onClick={() => toggleSetComplete(exIdx, setIdx)} className="flex justify-center items-center h-full active:scale-90 transition-transform">
                        {set.completada ? (
                          <div className="bg-green-500 rounded p-1 shadow-lg shadow-green-500/30"><CheckCircle2 className="w-4 h-4 text-black" /></div>
                        ) : (
                          <div className="bg-neutral-800 rounded p-1"><CheckCircle2 className="w-4 h-4 text-neutral-500" /></div>
                        )}
                      </button>
                      
                      <button onClick={() => removeSet(exIdx, setIdx)} className="flex justify-center items-center h-full text-neutral-600 hover:text-red-500 transition-colors p-1">
                         <Trash2 className="w-4 h-4" />
                      </button>
                    </div>
                  );
                })}
              </div>
              
              <button 
                onClick={() => addSet(exIdx)} 
                className="w-full py-2 flex items-center justify-center text-blue-500 font-bold text-sm bg-blue-500/10 rounded-xl mt-2 hover:bg-blue-500/20 transition-colors border border-dashed border-blue-500/20"
              >
                <Plus className="w-4 h-4 mr-1" /> Añadir Serie
              </button>
              
              <div className="border-b border-neutral-800 pt-4"></div>
            </div>
          );
        })}
      </div>

      <div className="mt-8">
        <button 
          onClick={() => setShowCatalog(true)}
          className="w-full py-4 flex items-center justify-center text-blue-400 font-bold text-lg border-2 border-dashed border-blue-500/30 rounded-2xl bg-blue-500/5 hover:bg-blue-500/10 hover:border-blue-500/50 transition-colors"
        >
          <Plus className="w-6 h-6 mr-2" /> Añadir Ejercicio
        </button>
      </div>

      {/* Floating Rest Timer */}
      <AnimatePresence>
        {isResting && (
          <motion.div
            initial={{ opacity: 0, y: 50, scale: 0.9 }}
            animate={{ opacity: 1, y: 0, scale: 1 }}
            exit={{ opacity: 0, y: 50, scale: 0.9 }}
            className="fixed bottom-6 left-1/2 -translate-x-1/2 bg-blue-600 text-white px-5 py-2.5 rounded-full shadow-2xl flex items-center space-x-3 z-40 border border-blue-400/30"
          >
            <Timer className="w-5 h-5 animate-pulse" />
            <div className="font-bold tracking-wider text-lg min-w-[50px] text-center">{restTimeStr}</div>
            <button 
              onClick={closeRestTimer} 
              className="bg-white/20 hover:bg-white/30 rounded-full p-1.5 transition-colors active:scale-90"
            >
              <X className="w-4 h-4" />
            </button>
          </motion.div>
        )}
      </AnimatePresence>

      {/* Catalog Modal */}
      <AnimatePresence>
        {showCatalog && (
          <motion.div 
            initial={{ opacity: 0, y: 50 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: 50 }}
            className="fixed inset-0 z-50 bg-black flex flex-col"
          >
            <div className="flex items-center justify-between p-4 border-b border-neutral-900">
              <h3 className="text-white font-bold text-xl">Añadir Ejercicio</h3>
              <button onClick={() => setShowCatalog(false)} className="text-neutral-400 p-2 bg-neutral-900 rounded-full hover:text-white transition-colors">
                <X className="w-6 h-6" />
              </button>
            </div>
            
            <div className="p-4 border-b border-neutral-900 bg-neutral-900/50">
              <div className="relative">
                <Search className="w-5 h-5 text-neutral-500 absolute left-3 top-1/2 -translate-y-1/2" />
                <input 
                  type="text" 
                  value={searchTerm}
                  onChange={e => setSearchTerm(e.target.value)}
                  placeholder="Buscar ejercicios..." 
                  className="w-full bg-neutral-900 text-white rounded-xl py-3 pl-10 pr-4 outline-none border border-neutral-800 focus:border-blue-500 transition-colors"
                />
              </div>
            </div>

            <div className="flex-1 overflow-y-auto p-4 space-y-2">
              {filteredCatalog.map((ej: any) => (
                <button 
                  key={ej.id_ejercicio} 
                  onClick={() => addExercise(ej)}
                  className="w-full flex items-center justify-between bg-neutral-900/50 hover:bg-neutral-800 p-4 rounded-2xl transition-colors text-left border border-transparent hover:border-neutral-700"
                >
                  <div className="flex items-center">
                    <div className="w-12 h-12 bg-blue-500/10 rounded-full flex items-center justify-center text-blue-500 mr-4 shrink-0 border border-blue-500/20">
                      {ej.imagen_url ? (
                        <img src={ej.imagen_url} className="w-full h-full object-cover rounded-full" />
                      ) : (
                        <Dumbbell className="w-6 h-6" />
                      )}
                    </div>
                    <div>
                      <div className="text-white font-bold">{ej.nombre}</div>
                      <div className="text-neutral-500 text-sm">{ej.grupo_muscular}</div>
                    </div>
                  </div>
                  <Plus className="w-5 h-5 text-blue-500" />
                </button>
              ))}
            </div>
          </motion.div>
        )}
      </AnimatePresence>

      {/* RPE Drawer */}
      <AnimatePresence>
        {rpeDrawerOpen && (
          <>
            <motion.div 
              initial={{ opacity: 0 }} 
              animate={{ opacity: 1 }} 
              exit={{ opacity: 0 }} 
              onClick={closeRpeDrawer}
              className="fixed inset-0 bg-black/60 z-40 backdrop-blur-sm"
            />
            <motion.div
              initial={{ y: "100%" }}
              animate={{ y: 0 }}
              exit={{ y: "100%" }}
              transition={{ type: "spring", damping: 25, stiffness: 200 }}
              className="fixed bottom-0 left-0 right-0 bg-neutral-900 border-t border-neutral-800 rounded-t-3xl z-50 p-6 shadow-2xl"
            >
              <div className="w-12 h-1.5 bg-neutral-700 rounded-full mx-auto mb-6"></div>
              
              <div className="text-center mb-6">
                <h3 className="text-white font-bold text-lg mb-1">Registrar RPE de la Serie</h3>
              </div>

              <div className="text-center mb-8">
                <div className="text-6xl font-black text-white">
                  {tempRpe !== null ? tempRpe : '0'}
                </div>
                <div className="text-neutral-500 text-sm font-medium mt-2">Seleccionar RPE</div>
              </div>

              <div className="overflow-x-auto pb-4 mb-4 hide-scrollbar">
                <div className="flex space-x-2 px-1">
                  {rpeValues.map(val => (
                    <button
                      key={val}
                      onClick={() => setTempRpe(val)}
                      className={`flex-none w-14 h-12 rounded-xl font-bold text-sm transition-all ${
                        tempRpe === val 
                          ? 'bg-blue-600 text-white ring-2 ring-blue-400 ring-offset-2 ring-offset-neutral-900' 
                          : 'bg-neutral-800 text-neutral-300 hover:bg-neutral-700'
                      }`}
                    >
                      {val}
                    </button>
                  ))}
                </div>
              </div>

              <button 
                onClick={saveRpe}
                className="w-full bg-neutral-700 hover:bg-neutral-600 text-white font-bold py-4 rounded-xl flex justify-center items-center transition-colors"
              >
                Listo <CheckCircle2 className="w-5 h-5 ml-2" />
              </button>
            </motion.div>
          </>
        )}
      </AnimatePresence>

      {/* Set Type Drawer */}
      <AnimatePresence>
        {typeDrawerOpen && (
          <>
            <motion.div 
              initial={{ opacity: 0 }} 
              animate={{ opacity: 1 }} 
              exit={{ opacity: 0 }} 
              onClick={closeTypeDrawer}
              className="fixed inset-0 bg-black/60 z-40 backdrop-blur-sm"
            />
            <motion.div
              initial={{ y: "100%" }}
              animate={{ y: 0 }}
              exit={{ y: "100%" }}
              transition={{ type: "spring", damping: 25, stiffness: 200 }}
              className="fixed bottom-0 left-0 right-0 bg-neutral-900 border-t border-neutral-800 rounded-t-3xl z-50 p-6 shadow-2xl"
            >
              <div className="w-12 h-1.5 bg-neutral-700 rounded-full mx-auto mb-6"></div>
              
              <div className="text-center mb-6">
                <h3 className="text-white font-bold text-lg mb-1">Tipo de Serie</h3>
              </div>

              <div className="space-y-3 mb-6">
                <button onClick={() => saveType('W')} className="w-full flex items-center p-4 rounded-xl bg-neutral-800 hover:bg-neutral-700 transition-colors border border-transparent hover:border-amber-500/30">
                  <div className="w-10 h-10 rounded-lg bg-amber-500/20 text-amber-500 flex items-center justify-center font-bold mr-4 text-xl">W</div>
                  <div className="text-white font-bold text-lg">Calentamiento</div>
                </button>
                <button onClick={() => saveType('N')} className="w-full flex items-center p-4 rounded-xl bg-neutral-800 hover:bg-neutral-700 transition-colors border border-transparent hover:border-neutral-500/30">
                  <div className="w-10 h-10 rounded-lg bg-neutral-600/30 text-white flex items-center justify-center font-bold mr-4 text-xl">N</div>
                  <div className="text-white font-bold text-lg">Normal</div>
                </button>
                <button onClick={() => saveType('D')} className="w-full flex items-center p-4 rounded-xl bg-neutral-800 hover:bg-neutral-700 transition-colors border border-transparent hover:border-indigo-400/30">
                  <div className="w-10 h-10 rounded-lg bg-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold mr-4 text-xl">D</div>
                  <div className="text-white font-bold text-lg">Drop Set</div>
                </button>
                <button onClick={() => saveType('F')} className="w-full flex items-center p-4 rounded-xl bg-neutral-800 hover:bg-neutral-700 transition-colors border border-transparent hover:border-red-500/30">
                  <div className="w-10 h-10 rounded-lg bg-red-500/20 text-red-500 flex items-center justify-center font-bold mr-4 text-xl">F</div>
                  <div className="text-white font-bold text-lg">Al Fallo</div>
                </button>
              </div>
            </motion.div>
          </>
        )}
      </AnimatePresence>

    </div>
  );
}
