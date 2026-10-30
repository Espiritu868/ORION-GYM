"use client";

import { useEffect, useState } from "react";
import { useRouter, usePathname } from "next/navigation";
import Link from "next/link";
import { Home, Activity, Calendar, LogOut, User as UserIcon } from "lucide-react";
import { motion, AnimatePresence } from "framer-motion";

export default function DashboardLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  const router = useRouter();
  const pathname = usePathname();
  const [clientInfo, setClientInfo] = useState<any>(null);

  useEffect(() => {
    const token = localStorage.getItem("client_token");
    const info = localStorage.getItem("client_info");
    if (!token || !info) {
      router.push("/");
    } else {
      setClientInfo(JSON.parse(info));
    }
  }, [router]);

  const handleLogout = () => {
    localStorage.removeItem("client_token");
    localStorage.removeItem("client_info");
    router.push("/");
  };

  const navItems = [
    { name: "Inicio", path: "/dashboard", icon: Home },
    { name: "Mi Progreso", path: "/dashboard/progreso", icon: Activity },
    { name: "Mis Rutinas", path: "/dashboard/rutinas", icon: Calendar },
  ];

  if (!clientInfo) return null; // loading state

  return (
    <div className="min-h-screen bg-neutral-950 flex flex-col">
      {/* Top Bar */}
      <header className="sticky top-0 z-50 bg-neutral-900/80 backdrop-blur-lg border-b border-neutral-800 px-6 py-4 flex items-center justify-between">
        <div className="flex items-center space-x-3">
          {clientInfo.fotografia ? (
            <img src={clientInfo.fotografia} alt="Profile" className="w-10 h-10 rounded-full border-2 border-amber-500 object-cover" />
          ) : (
            <div className="w-10 h-10 rounded-full bg-neutral-800 flex items-center justify-center border-2 border-amber-500">
              <UserIcon className="w-5 h-5 text-amber-500" />
            </div>
          )}
          <div>
            <h2 className="text-sm font-bold text-white leading-tight">{clientInfo.nombre_completo}</h2>
            <p className="text-xs text-amber-500 font-medium">Miembro Activo</p>
          </div>
        </div>
        <button onClick={handleLogout} className="p-2 text-neutral-400 hover:text-white transition-colors bg-neutral-800 rounded-full">
          <LogOut className="w-4 h-4" />
        </button>
      </header>

      {/* Main Content Area */}
      <main className="flex-1 px-4 py-6 overflow-x-hidden pb-24">
        <AnimatePresence mode="wait">
          <motion.div
            key={pathname}
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: -10 }}
            transition={{ duration: 0.2 }}
          >
            {children}
          </motion.div>
        </AnimatePresence>
      </main>

      {/* Bottom Navigation for Mobile */}
      <nav className="fixed bottom-0 left-0 right-0 bg-neutral-900/90 backdrop-blur-lg border-t border-neutral-800 pb-safe">
        <div className="flex justify-around items-center h-16">
          {navItems.map((item) => {
            const Icon = item.icon;
            const isActive = pathname === item.path;
            return (
              <Link
                key={item.path}
                href={item.path}
                className={`flex flex-col items-center justify-center w-full h-full space-y-1 relative ${
                  isActive ? "text-amber-500" : "text-neutral-500 hover:text-neutral-300"
                }`}
              >
                {isActive && (
                  <motion.div
                    layoutId="active-nav"
                    className="absolute top-0 w-12 h-1 bg-amber-500 rounded-b-full"
                  />
                )}
                <Icon className={`w-5 h-5 ${isActive ? "mb-1" : ""}`} />
                <span className="text-[10px] font-medium">{item.name}</span>
              </Link>
            );
          })}
        </div>
      </nav>
    </div>
  );
}
