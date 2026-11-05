import { Link } from 'react-router'
import { messages } from '../../shared/i18n/messages.fr.js'

export function NotFoundPage() {
  return (
    <main id="main" className="mx-auto max-w-2xl p-8 font-sans text-slate-900">
      <h1 className="text-2xl font-semibold">{messages.notFound.title}</h1>
      <p className="mt-4">
        <Link className="underline" to="/">{messages.notFound.back}</Link>
      </p>
    </main>
  )
}
