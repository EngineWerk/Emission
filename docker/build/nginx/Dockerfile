FROM nginx:1.11

RUN apt-get update && apt-get install vim curl -y

# configuration
COPY ./default.conf /etc/nginx/conf.d/default.conf
