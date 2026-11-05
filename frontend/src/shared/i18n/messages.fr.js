// Every UI string lives here (frontend-architecture §14, NFR-I18N-01). Placeholder copy for the dev page only.
export const messages = {
  devHome: {
    title: 'Environnement de développement',
    intro: "L'application React est servie par Nginx. Les fonctionnalités de la boutique seront ajoutées par le backlog de la phase 06.",
    apiBase: 'Base de l’API',
    backend: 'Backend (/up)',
    health: { checking: 'Vérification…', up: 'Disponible', down: 'Indisponible' },
    routeTest: 'Tester une route côté client',
  },
  notFound: {
    title: 'Page introuvable',
    back: 'Retour à l’accueil',
  },
  error: {
    title: 'Une erreur est survenue',
    body: 'Veuillez recharger la page.',
  },
}
