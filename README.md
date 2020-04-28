# morty-counts

Teaching Morty how to count and sending the results to Prometheus. + High Availability Option

![](https://media.giphy.com/media/W35DnRbN4oDHIAApdk/giphy.gif)
![](https://media.giphy.com/media/RH1IFq2GT0Oau8NRWX/giphy.gif)

## Requirements

1. `Redis` (the service & the PHP extension): to act as a buffer before push and avoid latency in the code

2. `bin/console comsave:prometheus:push` cronjob: to push data periodically to Prometheus Pushgateway

## How does it work?

### Single-node Prometheus + Pushgateway

![](./images/basic_prometheus_cluster_setup.png)

### Multi-node Prometheus + Pushgateway

todo details

![](./images/advanced_prometheus_cluster_setup.png)

## Development

Start `docker-compose up --remove-orphans -d --build`

Tests `docker exec $(docker ps | grep _php | awk '{print $1}') vendor/bin/phpunit tests`

## License

MIT

![](https://media.giphy.com/media/e6tJpLvjY8jXa/giphy.gif)
