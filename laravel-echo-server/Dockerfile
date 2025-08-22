# Use official Node.js 20 image
FROM node:20

# Set working directory
WORKDIR /app

# Copy package files and install global Echo server
COPY package*.json ./
RUN npm install -g laravel-echo-server

# Copy the rest of the application
COPY . .

# Expose the default Echo port
EXPOSE 6001

# Run Laravel Echo Server in force mode to avoid conflicts
CMD ["laravel-echo-server", "start", "--force"]
