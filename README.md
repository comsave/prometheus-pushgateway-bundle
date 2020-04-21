# morty-counts

Teaching Morty how to count and sending the results to Prometheus

![](https://media.giphy.com/media/e6tJpLvjY8jXa/giphy.gif)

## Requirements

1. `Redis` (the service & the PHP extension): to act as a buffer before and avoid latency in the code

2. `bin/console comsave:prometheus:push` cronjob: to push data periodically to Prometheus Pushgateway

## License

MIT
