import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  // Allow connections from the local network
  allowedDevOrigins: ['192.168.1.43', '192.168.1.254'],
  async rewrites() {
    const apiDest = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8080';
    return [
      {
        source: '/api/:path*',
        destination: `${apiDest}/api/:path*`,
      },
    ];
  },
};

export default nextConfig;
