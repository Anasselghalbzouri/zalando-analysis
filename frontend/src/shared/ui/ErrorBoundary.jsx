import { Component } from 'react'
import { messages } from '../i18n/messages.fr.js'

// Last-resort boundary for render errors (frontend-architecture §10). Shows a French message instead of a blank page.
export class ErrorBoundary extends Component {
  state = { error: null }

  static getDerivedStateFromError(error) {
    return { error }
  }

  componentDidCatch(error, info) {
    console.error(error, info.componentStack)
  }

  render() {
    if (this.state.error) {
      return (
        <main role="alert" className="mx-auto max-w-2xl p-8 font-sans text-slate-900">
          <h1 className="text-2xl font-semibold">{messages.error.title}</h1>
          <p className="mt-4">{messages.error.body}</p>
        </main>
      )
    }
    return this.props.children
  }
}
