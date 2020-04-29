# Morty Counts!

## Symfony Prometheus + PushGateway integration

Teaching Morty how to count and sending the results to Prometheus. +High Availability Setup 

## Requirements

1. `Redis` (the service & the PHP extension): to act as a buffer before push and avoid latency in the code

2. `bin/console comsave:prometheus:push` cronjob: to push data periodically to Prometheus Pushgateway

## How does it work?

### Single Node Prometheus + Pushgateway

Single node is pretty straightforward. 

1. Use `PushGatewayClient` to create a metric. Metric is stored in `Redis`.
2. Use `PushGatewayClient` can be pushed manually or with a command. After push metrics stored in Redis are transported to the actual `PushGateway` service.
3. `Prometheus` periodically pulls in new metrics from `PushGateway`.

![](./images/basic_prometheus_cluster_setup.png)

### Multi-Node Prometheus + Pushgateway Cluster

Multi-node set up works with the basics described above, with a couple exceptions:

1. There's an `Haproxy` (or other load balancer) that decides which `PushGateway` will receive the `push`.
2. Each `Prometheus` in every node pulls from every `PushGateway`. That way each `Prometheus` has the latest metrics.
3. Each `Prometheus` pulls (federates) from another 2 `Prometheus` nodes but less often. This ensures data integrity (sort of replication).

![](./images/advanced_prometheus_cluster_setup.png)

## Development

Start single node `docker-compose up -d`

Or multi node     `docker-compose up -f docker-compose.multi-node.yml -d`

Tests `docker exec $(docker ps | grep _php | awk '{print $1}') vendor/bin/phpunit tests`

![](https://media.giphy.com/media/W35DnRbN4oDHIAApdk/giphy.gif)
![](https://media.giphy.com/media/RH1IFq2GT0Oau8NRWX/giphy.gif)

## License

MIT

![](https://media.giphy.com/media/e6tJpLvjY8jXa/giphy.gif)
