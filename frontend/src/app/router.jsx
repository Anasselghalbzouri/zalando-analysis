import { createBrowserRouter } from 'react-router'
import { DevHomePage } from './routes/DevHomePage.jsx'
import { NotFoundPage } from './routes/NotFoundPage.jsx'

// Placeholder routes only. The storefront routes (frontend-architecture §3) are added by the Phase 06 stories.
export const router = createBrowserRouter([
  { path: '/', element: <DevHomePage /> },
  { path: '*', element: <NotFoundPage /> },
])
