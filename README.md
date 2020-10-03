# pi-radio
Webradio for Raspberry PI

Start application with

    docker-compose up

## Backend
![Backend](https://github.com/ojooss/piradio/workflows/Backend/badge.svg)

### build images

    # default
    docker build --tag piradio-backend:latest .
    
    # RaspberryPI
    docker build --tag piradio-backend:latest --platform linux/arm/v7 .

### Initialise
To create an initial admin user call

    curl --location --request POST 'https://localhost:8090/setup/admin-user' \
    --header 'Content-Type: application/json' \
    --data-raw '{
        "username": "admin",
        "password": "admin123"
    }'

see api documentation on https://localhost/api/docs

## Frontend

### build images

    # default
    docker build --tag piradio-frontend:latest .
    
    # RaspberryPI
    docker build --tag piradio-frontend:latest --platform linux/arm/v7 .
