import { configureStore } from '@reduxjs/toolkit'
import { api } from './api.js'

// Server state lives in RTK Query; client slices (ui, clock) are added with their stories (frontend-architecture §7).
export const store = configureStore({
  reducer: {
    [api.reducerPath]: api.reducer,
  },
  middleware: (getDefaultMiddleware) => getDefaultMiddleware().concat(api.middleware),
})
