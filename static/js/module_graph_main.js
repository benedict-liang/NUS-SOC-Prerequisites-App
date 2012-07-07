$(document).ready(function() {

    var width = $('#graph_canvas').width();
    var height = $(window).height() * (80 / 100);
    var g = new Graph();
    var linecolors = ["#000000", "#999999", "#990000"]
    g.edgeFactory.template.style.directed = true;

    setup_graph = function(mod, base_url){
        var module_code = mod;

        $.getJSON(base_url + 'modules/get_module_list?module_code=' + module_code, function(data) {
            
            if(data === null){
                populate_graph_singular(module_code);
            }
            else{
                populate_graph(data);
            }

        });
    }

    function populate_graph_singular(mod){

        g.addNode(mod);

        var renderer = new Graph.Renderer.Raphael('graph_canvas', g, width, height);
    }

    function populate_graph(data){

        $.each(data, function(index, value){
            g.addEdge(value[0], value[1], {stroke : linecolors[value[2]]});

        });

        var layouter = new Graph.Layout.Ordered(g, topological_sort(g));
        var renderer = new Graph.Renderer.Raphael('graph_canvas', g, width, height);
    }
    
});
