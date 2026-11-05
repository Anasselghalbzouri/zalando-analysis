import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'

// Dev server runs in the "vite" container and is reached only through Nginx (single origin, ADR-003).
export default defineConfig({
  plugins: [react(), tailwindcss()],
  server: {
    host: '0.0.0.0',
    port: 5173,
    strictPort: true,
    // The browser connects to HMR through Nginx on the published port.
    hmr: { clientPort: Number(process.env.VITE_HMR_CLIENT_PORT ?? 8080) },
    // Polling is required for file watching on Windows-drive bind mounts.
    watch: { usePolling: process.env.VITE_USE_POLLING === 'true' },
  },
})
