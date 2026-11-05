import { useEffect, useState } from 'react'
import { Link } from 'react-router'
import { API_BASE } from '../api.js'
import { messages } from '../../shared/i18n/messages.fr.js'

// Development placeholder: proves the SPA, the router and the Nginx route to Laravel's /up work together.
export function DevHomePage() {
  const [health, setHealth] = useState('checking')

  useEffect(() => {
    fetch('/up', { headers: { Accept: 'text/html' } })
      .then((response) => setHealth(response.ok ? 'up' : 'down'))
      .catch(() => setHealth('down'))
  }, [])

  return (
    <main id="main" className="mx-auto max-w-2xl p-8 font-sans text-slate-900">
      <h1 className="text-2xl font-semibold">{messages.devHome.title}</h1>
      <p className="mt-4">{messages.devHome.intro}</p>
      <dl className="mt-6 grid grid-cols-[max-content_1fr] gap-x-4 gap-y-2">
        <dt className="font-medium">{messages.devHome.apiBase}</dt>
        <dd><code>{API_BASE}</code></dd>
        <dt className="font-medium">{messages.devHome.backend}</dt>
        <dd role="status" data-testid="backend-health">{messages.devHome.health[health]}</dd>
      </dl>
      <p className="mt-6">
        <Link className="underline" to="/route-de-test-client">{messages.devHome.routeTest}</Link>
      </p>
    </main>
  )
}
