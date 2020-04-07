# Foreweather Notify

Foreweather Notify; Foreweather Abonelerine günlük bildirim gönderen servisidir. 
 
# Kurulum

Foreweather Notify'ı keşfetmek istiyorsanız, geliştirme ortamını kullanmak iyi bir seçim olabilir. 

## Docker

Foreweather Notify'ın geliştirme sürümünü başlatmanın hızlı bir yolu github reposunu klonlamak ve aşağıdaki 
komutları çalıştırmaktır:

### Docker

```bash

docker rm -f beans
docker run -d --name beans uretgec/beanstalkd-alpine:latest

docker build --no-cache -t zekiunal/foreweather-notify .
docker push zekiunal/foreweather-notify

docker rm -f foreweather-notify
docker run -d --name foreweather-notify \
    -v $PWD/src:/www \
    -e QUEUE_HOST="beans" \
    -e API_BASE_URL="http://api" \
    --link beans:beans \
    --link fore_api:api \
    zekiunal/foreweather-notify
    
 docker logs -f foreweather-notify
 
```
