# Stage 1: Build the React application
FROM node:20-alpine AS builder

# Set the working directory
WORKDIR /app

ARG REACT_APP_BACKEND_URL
ARG REACT_APP_REVERB_APP_KEY
ARG REACT_APP_REVERB_HOST
ARG REACT_APP_REVERB_PORT
ARG REACT_APP_REVERB_SCHEME

# --- Set build-time environment variables ---
ENV REACT_APP_BACKEND_URL=$REACT_APP_BACKEND_URL
ENV REACT_APP_REVERB_APP_KEY=$REACT_APP_REVERB_APP_KEY
ENV REACT_APP_REVERB_HOST=$REACT_APP_REVERB_HOST
ENV REACT_APP_REVERB_PORT=$REACT_APP_REVERB_PORT
ENV REACT_APP_REVERB_SCHEME=$REACT_APP_REVERB_SCHEME

COPY package.json ./
COPY package-lock.json ./

# Install dependencies
RUN npm install
# If using yarn:
# RUN yarn install

# Copy the rest of the application source code
COPY . .

# Build the application for production
RUN npm run build

# Stage 2: Serve the application using Nginx
FROM nginx:stable-alpine

# Copy custom nginx configuration
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Copy the build output from the builder stage
COPY --from=builder /app/build /usr/share/nginx/html

RUN chown -R nginx:nginx /usr/share/nginx/html && chmod -R 755 /usr/share/nginx/html

EXPOSE 3000

# Start Nginx when the container launches
CMD ["nginx", "-g", "daemon off;"]