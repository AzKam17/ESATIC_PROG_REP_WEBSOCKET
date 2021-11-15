//initiate canvas
let canvas = new fabric.Canvas('myCanvas');
$(".pen-range").val(1);


$(document).ready(function() {
    canvasFunctions();

    adjustSize()();

    $(window).resize(function() {
        adjustSize()();
    });
});

function canvasFunctions(){

    //Couleur du pinceau
    let color = 'black';
    let x0;
    let y0;
    canvas.backgroundColor = 'white';

    //Nettoyer le canvas
    $("#Clear").click(function () {
        canvas.clear();
    });

    //Changement de couleur du pinceau
    $(".color").click(function () {
        color = $(this).attr('id');
        canvas.freeDrawingBrush.color = color;
    });

    $(".pen-range").change(function (){
        canvas.freeDrawingBrush.width = $(this).val();
    });

    canvas.observe('mouse:down', function(options){
        let pointer = canvas.getPointer(options.e);
        x0 = pointer.x; //Point initial de X
        y0 = pointer.y; //Point initial de y
        //Ligne libre
        canvas.isDrawingMode = true;

        saveCanvas();

    });
    
    canvas.observe('mouse:up', function () {
        x0 = y0 = 0;
    });


    function saveCanvas() {
        let canvasContents = canvas.toDataURL();
        let data = {image: canvasContents};
        var string = JSON.stringify(data);
        console.log(canvasContents);
    }
}

function adjustSize() {
    return function() {
        var menuContainer = $('.container');
        var canvasWrapper = $('.wrapper');
        var canvasContainer = $('.canvas-container');
        var allCanvas = $('canvas');

        // reset to 100%, so adjustment calculations do not break
        canvasWrapper.css('height', '100%');

        // adjust
        canvasWrapper.css('width', menuContainer.outerWidth(false));
        canvasWrapper.css('height', canvasWrapper.height() - menuContainer.height());
        canvasWrapper.css('margin-left', menuContainer.css('margin-left'));

        // adjust Fabric.js
        canvasContainer.css('width', '100%');
        canvasContainer.css('height', '100%');

        canvas.setWidth(canvasContainer.width());
        canvas.setHeight(canvasContainer.height());
        canvas.calcOffset();
    };
};
