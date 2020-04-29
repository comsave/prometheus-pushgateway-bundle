from diagrams import Cluster, Diagram, Edge
from diagrams.onprem.compute import Server
from diagrams.onprem.inmemory import Redis
from diagrams.onprem.monitoring import Prometheus
from diagrams.onprem.network import Haproxy
from diagrams.aws.compute import ECS

with Diagram(name="Advanced Prometheus Cluster Setup", show=False):
    haproxy = Haproxy("haproxy")

    with Cluster("App Cluster"):
        app = Server("app")
        app_redis = Redis("pushgateway_redis_buffer")
        app - Edge(color="brown", style="dashed") - app_redis

        app_cluster = [
            app,
            app_redis
        ]

    with Cluster("Prometheus Cluster"):
        with Cluster("Prom1"):
            push1 = ECS('pushgateway')
            prom1 = Prometheus('prometheus')

        with Cluster("Prom2"):
            push2 = ECS('pushgateway')
            prom2 = Prometheus('prometheus')

        with Cluster("Prom3"):
            push3 = ECS('pushgateway')
            prom3 = Prometheus('prometheus')

        push1 << Edge(label="pull", color="brown") << prom1
        push1 << Edge(color="brown") << prom2
        push1 << Edge(color="brown") << prom3

        push2 << Edge(color="brown") << prom1
        push2 << Edge(label="pull", color="brown") << prom2
        push2 << Edge(color="brown") << prom3

        push3 << Edge(color="brown") << prom1
        push3 << Edge(color="brown") << prom2
        push3 << Edge(label="pull", color="brown") << prom3

        prom1 << Edge(label="pull") << prom2
        prom1 << Edge(label="pull") << prom3

        prom2 << Edge(label="pull") << prom1
        prom2 << Edge(label="pull") << prom3

        prom3 << Edge(label="pull") << prom1
        prom3 << Edge(label="pull") << prom2

    app >> Edge(label="push_metrics") >> haproxy >> [
        push1,
        push2,
        push3
    ]

with Diagram(name="Basic Prometheus Cluster Setup", show=False):
    with Cluster("App Cluster"):
        app = Server("app")
        app_redis = Redis("pushgateway_redis_buffer")
        app - Edge(color="brown", style="dashed") - app_redis

        app_cluster = [
            app,
            app_redis
        ]

    with Cluster("Prometheus Cluster"):
        push1 = ECS('pushgateway')
        prom1 = Prometheus('prometheus')

    push1 << Edge(label="pull") << prom1

    app >> Edge(label="push_metrics") >> push1