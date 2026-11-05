# Vite dev server for the React SPA (local development only; docker-architecture §1).
FROM node:24-alpine

COPY --chmod=755 docker/node/entrypoint.sh /usr/local/bin/vite-entrypoint

# node_modules lives in a named volume; creating the mount point here gives it the right owner.
WORKDIR /app
RUN mkdir -p /app/node_modules && chown -R node:node /app
USER node

ENTRYPOINT ["vite-entrypoint"]
CMD ["npm", "run", "dev"]
