import type { Metadata } from "next";
import { Inter } from "next/font/google";
import "./globals.css";

const inter = Inter({ subsets: ["latin"] });

export const metadata: Metadata = {
  title: "Orion Gym - Portal de Clientes",
  description: "Portal exclusivo para visualizar progreso y rutinas.",
  manifest: "/manifest.json",
};

export const viewport = {
  themeColor: "#ff8c00",
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="es" className="dark">
      <body className={`${inter.className} bg-neutral-950 text-neutral-100 antialiased selection:bg-amber-500/30`}>
        {children}
      </body>
    </html>
  );
}
