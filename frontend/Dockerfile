# Etapa 1: Construccion de la SPA con Node
FROM node:20-alpine AS build
WORKDIR /app

COPY package*.json ./
RUN npm ci || npm install

COPY . .
RUN npm run build

# Etapa 2: Servidor Nginx de produccion
FROM nginx:alpine
COPY --from=build /app/dist /usr/share/nginx/html
COPY nginx.conf.template /etc/nginx/templates/default.conf.template

ENV BACKEND_URL=http://backend:80

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
