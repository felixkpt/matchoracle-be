# Step 1: Build the app using a Node image
FROM node:16 AS build

# Set the working directory
WORKDIR /app

# Copy package files and install dependencies
COPY package*.json ./
RUN npm install

# Copy the rest of the application files
COPY . .

# Build the React application
RUN npm run build

# Step 2: Serve the app using Nginx
FROM nginx:alpine

COPY docker/nginx.conf /etc/nginx/nginx.conf

# Copy the build output to the Nginx HTML folder
COPY --from=build /app/dist /usr/share/nginx/html

# Copy the startup script
COPY docker/startup.sh /startup.sh

# Make the startup script executable
RUN chmod +x /startup.sh

# Expose port 3080
EXPOSE 3080

# Set the startup script as the entry point
ENTRYPOINT ["/startup.sh"]
