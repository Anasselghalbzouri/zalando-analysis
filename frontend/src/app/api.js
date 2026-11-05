import { createApi, fetchBaseQuery } from '@reduxjs/toolkit/query/react'

// Same-origin REST API (api-contracts §1). Overridable with VITE_API_BASE (frontend/.env.example).
export const API_BASE = import.meta.env.VITE_API_BASE ?? '/api/v1'

// Root RTK Query slice (ADR-006). Feature modules add endpoints with api.injectEndpoints().
// CSRF bootstrap, the 419 retry and the 401 redirect are built with authentication (US-002).
export const api = createApi({
  reducerPath: 'api',
  baseQuery: fetchBaseQuery({
    baseUrl: API_BASE,
    credentials: 'same-origin',
    prepareHeaders: (headers) => {
      headers.set('Accept', 'application/json')
      return headers
    },
  }),
  endpoints: () => ({}),
})
