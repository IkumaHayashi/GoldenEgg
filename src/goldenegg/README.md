# 起動方法
## ChromeDriverを起動しておく
下記コマンドで起動できる。ただし、`.env`の`CHROME_DRIVER_URL`とポートを合わせること。
`docker-compose exec app chromedriver --verbose --port=4000 > /dev/null 2>&1 &`