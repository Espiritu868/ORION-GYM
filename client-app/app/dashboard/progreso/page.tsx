"use client";

import { useEffect, useState } from "react";
import { Activity, TrendingDown, TrendingUp, Minus } from "lucide-react";
import { motion } from "framer-motion";
import {
  LineChart,
  Line,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  ResponsiveContainer
} from "recharts";

export default function ProgresoPage() {
  const [historial, setHistorial] = useState<any[]>([]);
  const [ultimo, setUltimo] = useState<any>(null);
  const [diferencias, setDiferencias] = useState<any>({});
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const info = localStorage.getItem("client_info");
    if (info) {
      const parsed = JSON.parse(info);
      fetchProgress(parsed.id_cliente);
    }
  }, []);

  const fetchProgress = async (id_cliente: string) => {
    try {
      const res = await fetch(`/api/client_progress.php?id_cliente=${id_cliente}`);
      const data = await res.json();
      if (data.success) {
        setHistorial(data.historial);
        setUltimo(data.ultimo_registro);
        setDiferencias(data.diferencias);
      }
    } catch (error) {
      console.error(error);
    } finally {
      setLoading(false);
    }
  };

  const chartData = historial.map((h) => ({
    name: new Date(h.fecha_evaluacion).toLocaleDateString('es-ES', { month: 'short', day: 'numeric' }),
    peso: parseFloat(h.peso),
  }));

  const renderDif = (val: number) => {
    if (!val) return <span className="text-neutral-500 flex items-center text-xs"><Minus className="w-3 h-3 mr-1" /> 0</span>;
    if (val > 0) return <span className="text-red-400 flex items-center text-xs"><TrendingUp className="w-3 h-3 mr-1" /> +{val}</span>;
    return <span className="text-green-400 flex items-center text-xs"><TrendingDown className="w-3 h-3 mr-1" /> {val}</span>;
  };

  if (loading) return <div className="text-center text-neutral-400 mt-10">Cargando progreso...</div>;

  if (!ultimo) {
    return (
      <div className="text-center text-neutral-400 mt-20 space-y-4">
        <Activity className="w-12 h-12 mx-auto text-neutral-600" />
        <p>Aún no tienes evaluaciones registradas.</p>
        <p className="text-sm">Habla con tu coach para tu primera medición.</p>
      </div>
    );
  }

  return (
    <div className="space-y-8 pb-10">
      <div className="flex items-center space-x-3">
        <div className="bg-blue-500/10 p-3 rounded-xl">
          <Activity className="w-6 h-6 text-blue-500" />
        </div>
        <div>
          <h1 className="text-2xl font-bold text-white">Mi Progreso</h1>
          <p className="text-sm text-neutral-400">Última evaluación: {new Date(ultimo.fecha_evaluacion).toLocaleDateString('es-ES')}</p>
        </div>
      </div>

      {/* Weight Chart */}
      <div className="bg-neutral-900 border border-neutral-800 rounded-3xl p-5">
        <div className="flex justify-between items-end mb-6">
          <div>
            <p className="text-neutral-400 text-sm font-medium">Peso Actual</p>
            <h2 className="text-3xl font-black text-white">{ultimo.peso} <span className="text-lg text-neutral-500 font-normal">kg</span></h2>
          </div>
          <div className="bg-neutral-800 px-3 py-1.5 rounded-lg">
            {renderDif(diferencias?.peso)}
          </div>
        </div>
        <div className="h-48 w-full">
          <ResponsiveContainer width="100%" height="100%">
            <LineChart data={chartData}>
              <CartesianGrid strokeDasharray="3 3" stroke="#262626" vertical={false} />
              <XAxis dataKey="name" stroke="#525252" fontSize={10} tickLine={false} axisLine={false} />
              <YAxis stroke="#525252" fontSize={10} tickLine={false} axisLine={false} domain={['dataMin - 2', 'dataMax + 2']} />
              <Tooltip 
                contentStyle={{ backgroundColor: '#171717', border: '1px solid #262626', borderRadius: '12px' }}
                itemStyle={{ color: '#3b82f6' }}
              />
              <Line type="monotone" dataKey="peso" stroke="#3b82f6" strokeWidth={3} dot={{ r: 4, fill: '#3b82f6', strokeWidth: 2, stroke: '#171717' }} activeDot={{ r: 6 }} />
            </LineChart>
          </ResponsiveContainer>
        </div>
      </div>

      {/* Body Schema */}
      <div>
        <h3 className="text-lg font-bold text-white mb-4">Medidas Corporales (cm)</h3>
        <div className="grid grid-cols-2 gap-4">
          {[
            { label: 'Pecho', key: 'pecho' },
            { label: 'Brazo', key: 'brazo' },
            { label: 'Cintura', key: 'cintura' },
            { label: 'Cadera', key: 'cadera' },
            { label: 'Pierna', key: 'pierna' },
            { label: 'Pantorrilla', key: 'pantorrilla' },
          ].map((item, idx) => (
            <motion.div 
              key={item.key}
              initial={{ opacity: 0, scale: 0.9 }}
              animate={{ opacity: 1, scale: 1 }}
              transition={{ delay: idx * 0.05 }}
              className="bg-neutral-900 border border-neutral-800 rounded-2xl p-4 flex flex-col"
            >
              <span className="text-neutral-400 text-xs font-bold uppercase tracking-wider mb-1">{item.label}</span>
              <div className="flex items-end justify-between mt-auto">
                <span className="text-xl font-bold text-white">{ultimo[item.key]}</span>
                <div className="bg-neutral-800/50 px-2 py-1 rounded-md">
                  {renderDif(diferencias?.[item.key])}
                </div>
              </div>
            </motion.div>
          ))}
        </div>
      </div>
      
      <div className="bg-amber-500/10 border border-amber-500/20 rounded-2xl p-4 text-center">
         <p className="text-amber-500 text-sm font-medium">Recuerda: Los cambios físicos toman tiempo y constancia. ¡No te rindas!</p>
      </div>

    </div>
  );
}
