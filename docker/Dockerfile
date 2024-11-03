# frontend/Dockerfile
FROM node:20

# Set working directory
WORKDIR /usr/src/app

# Copy application files
COPY . .

# Install dependencies
RUN npm install

# Build application
RUN npm run build

# Expose port
EXPOSE 3000

CMD ["npm", "start"]
