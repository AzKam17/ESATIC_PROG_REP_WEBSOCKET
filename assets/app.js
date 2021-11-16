
import './styles/bootstrap.css';
import './styles/app.css';
import './styles/animate.css';

// start the Stimulus application
import './bootstrap';

import './libs/compress';

//initiate canvas
let canvas = new fabric.Canvas('myCanvas');
$(".pen-range").val(1);

//Send drawing functions
let sendDrawing = () => {};

$(document).ready(function (){
    init();

    Object.defineProperty(String.prototype, 'removeSpaces', {
        get: function(){
            return this.toLowerCase().split(' ').join('');
        }
    })

    canvasFunctions();

    adjustSize()();

    $(window).resize(function() {
        adjustSize()();
    });

});

function init(){

    //State de l'app
    let state = {
        pseudo: false,
        lobby: false,
        room: false,
        newRoom: false,
        noConnection: false,
    }

    //Store pour stocker les messages
    let store = {};

    //Stocker le pseudo
    let pseudo = sessionStorage.getItem("pseudo") ?? '';

    //Code de la room actuelle
    let globalRoomCode = null;

    //On cache tout les éléments au départ
    $("#pseudo").hide();
    $("#lobby").hide();
    $("#newRoom").hide();
    setTimeout(() => {
        $("#room").hide();
    }, 500)

    /*
        Fonctions de la Pseudo Box
     */

    //Fonction pour enregistrer le pseudo
    const savePseudo = () => {
        let tempPseudo = $("#pseudoInput").val();
        const pseudoVerifCondition = (tempPseudo !== "") && (tempPseudo !== undefined) && (tempPseudo.length > 3);
        if( pseudoVerifCondition ){
            sessionStorage.setItem("pseudo", tempPseudo);
            updateState("lobby", true);
        }
    }

    //Click sur le bouton de validation du pseudo
    $("#validerPseudo").click(() => { savePseudo() });

    //Appuie sur la touche entrée dans le input de la Pseudo Box
    $("#pseudoInput").keydown((event) => {
        if(event.code === 'Enter'){
            savePseudo()
        }else{
            //CSS de l'input
            let selectorInputPseudoBox = "#pseudoInput";
            const tempPseudo = $(selectorInputPseudoBox).val();
            const pseudoVerifCondition = (tempPseudo !== "") && (tempPseudo !== undefined) && (tempPseudo.length > 3);
            let addC = (pseudoVerifCondition) ? "is-valid" : "is-invalid";
            let remC = (pseudoVerifCondition) ? "is-invalid" : "is-valid";
            $(selectorInputPseudoBox).addClass(addC).removeClass(remC);
        }
    })

    /*
        Fonctions de la Lobby Box
     */

    const initLobby = () => {
        //Récupération du pseudo pour affichage dans le lobby
        pseudo = sessionStorage.getItem("pseudo");
        $("#msgLobbyWelcome").text( "Bienvenue " + pseudo );
    }

    //Mise à jour des rooms
    const updateRooms = (data) => {
        //Message sur le nombre de rooms disponibles
        $("#nbRoomsDispo").text( `${data.length} salle${verifPluriel(data.length)} disponible${verifPluriel(data.length)}.` );
        $('#tableChoixRoom').text( "" );
        //Création du tableau de listing des rooms
        data.map((value, key) => {
            const e = $('<tr/>')
                .addClass("table-light")
                .append(
                    //Nom de la room
                    $("<th/>").addClass("row pb-3 pt-3").text(value.lib)
                )
                .append(
                    //Bouton rejoindre la room
                    $("<td/>").append(
                        $("<button/>")
                            .text("Rejoindre")
                            .attr("type", "button")
                            .addClass("btn btn-primary btn-sm room-btn mr-5")
                            //Au click on a la key de la room
                            .click(() => { enterRoom(value.lib.removeSpaces + "#" + value.id) })
                    )
                    .append(
                        "&nbsp;"
                    )
                    .append(
                        $("<button/>")
                            .text("Supprimer")
                            .attr("type", "button")
                            .addClass("btn btn-danger btn-sm room-btn ml-5")
                            //Au click on a la key de la room
                            .click(() => { deleteRoom(value.id) })
                    )
                );
            //Ajout dans le listing
            $('#tableChoixRoom').append(e);

            //Mise à jour du store global des messages et salles
            if(store[value.lib.removeSpaces + "#" + value.id] === undefined){
                store[value.lib.removeSpaces + "#" + value.id] = {
                    lib: value.lib,
                    paint: null,
                    messages: []
                }
                getRoomMessages(value.id);
            }
        })

    }

    const enterRoom = (roomCode) => {
        updateState("room", true, function (){
            //On met à jour le global room code pour envoyer et réceptionner les messages
            globalRoomCode = roomCode;
            //On va chercher le nom de la salle dans le store pour affichage
            $("#roomName").text( `${store[roomCode].lib}` )
            //Fonction pour mettre le canvas correctement
            try{
                canvasFunctions();
                adjustSize()();
                $(window).resize(function() {
                    adjustSize()();
                });
            }catch (e) {
                console.log("Canvas Err")
            }
        })

        actualizeRoom(true);
    }

    /*
        Fonctions de la Room Box
     */
    $(".returnLobby").click(function(){
       exitRoom();
    });

    //Appuie sur le bouton envoie
    $("#roomMessageSendBtn").click(function(){
        sendTextMessage()
    });

    //Appuie sur la touche entrée dans la zone de texte
    $("#roomInput").keydown(function (event){
        if(event.code === 'Enter'){
            sendTextMessage()
        }
    })

    const exitRoom = () => {
        globalRoomCode = null;
        canvas.clear();
        socket.send(JSON.stringify({id: null, sender: 'Salia', content: 'Hello', type: 'init', troom: null, room: null}));
        updateState("lobby", true);
    }

    /*
        Fonctions WebSocket
     */
    const socket = new WebSocket(`ws://${location.hostname}:9930`);

    socket.addEventListener("open", function() {
        console.log("Connexion réussie au serveur WebSocket");
        setTimeout(function(){
            socket.send(JSON.stringify({id: null, sender: 'Salia', content: 'Hello', type: 'init', troom: '', room: null}));
        }, 500)
    });

    socket.addEventListener("close", function(){
        console.log("La connexion au serveur WebSocket a été perdue");
        updateState("noConnection", true);
    });

    socket.addEventListener("error", function(){
        console.log("Une erreur a eu lieu lors de la communication avec le serveur Websocket");
        updateState("noConnection", true);
    });

    socket.addEventListener("message", function(e) { analyzeMessage(e.data) });

    //Fonction pour analyser les messages entrants
    const analyzeMessage = (data) => {
        try{
            //Tansformation du message reçu en JSON
            let rcvData = JSON.parse(data);
            //On récupére le type du premier objet
            const type = rcvData[0]['type'];

            //Actions en fonction du type d'objet
            switch(type){
                //Message d'initialisation du système
                case 'init':
                    updateRooms(rcvData);
                    break;
                case 'msg':
                    for(let i = 0; i < rcvData.length; i++){
                        saveIncomingMessage(rcvData[i].code, rcvData[i]);
                    }
                    actualizeRoom();
                    break;
                case 'canvas':
                    store[rcvData[0].code].paint = rcvData[0].content;
                    actualizeRoom(true);
                    break;
                case 'refreshRoomsList':
                    exitRoom();
                    break;
                case 'animate':
                    animateRoom(rcvData[0]);
                    break;
                default:
                    exitRoom();
                    break;
            }
        }catch (e) {
            //console.log(e);
        }
    }

    //Fonction pour envoyer un message
    const sendMessage = (obj, useGlobalRoomCode = false) => {
        let message = "";
        if(useGlobalRoomCode){
            message = {sender: pseudo, content: obj.content, type: obj.type, room: null, code: "" /*Récupérer le id depuis la concaténation*/}
        }else{
            message = {sender: pseudo, content: obj.content, type: obj.type, room: null, code: (globalRoomCode.split("#")[1]) /*Récupérer le id depuis la concaténation*/}
        }
        socket.send(JSON.stringify(message));
    }

    //Envoyer le message textuel du chat
    const sendTextMessage = () => {
        const selector = "#roomInput";
        if($(selector).val().length > 0){
            sendMessage(
                {
                    content: $(selector).val(),
                    type: 'msg'
                }
            )

            sendDrawing();
            $(selector).val("")
        }
    }

    //Récupérer tous les messages pour une room
    const getRoomMessages = (roomId) => {
        sendMessage(
            {
                content: roomId,
                type: 'getRoomMessages'
            }, true
        )
    }

    //Enregistrer les nouveaux messages
    const saveIncomingMessage = (roomCode, message) => {
        store[roomCode].messages.push(message);
    }

    //Actualiser la room actuelle si on est dans une room
    const actualizeRoom = (refreshCanvas = false) => {
        if(globalRoomCode !== null && globalRoomCode !== undefined){
            //Rangement des messages par ordre croissant
            store[globalRoomCode].messages.sort(function(a, b) {
                return a.id - b.id  ||  a.name.localeCompare(b.name);
            });
            let actualMessage = null;
            //On vide la chatbox
            $("#chatBox").html('');
            //On affiche les messages
            for(let i = 0; i < store[globalRoomCode].messages.length; i++){
                actualMessage = store[globalRoomCode].messages[i];
                $("#chatBox").append( (`${i > 0 ? '<br>' : ''}[<b>${actualMessage.sender}</b>] ${actualMessage.content}`) );
            }
            //On reste toujours au bottom pour afficher les derniers messages
            $("#chatBox").scrollTop(function() { return this.scrollHeight; });

            //canvas.objec = store[globalRoomCode].paint;
            //canvas.getO

            if(refreshCanvas){
                canvas.clear();
                canvas.loadFromJSON(store[globalRoomCode].paint, canvas.renderAll.bind(canvas));
            }

        }
    }

    //Créer une nouvelle salle
    const createNewRoom = () => {
        const newRoomName = $("#newRoomInput").val();
        if(state.newRoom && newRoomName.length > 3){
            sendMessage(
                {
                    content: newRoomName,
                    type: 'newRoom'
                }, true
            )
            setTimeout(exitRoom, 500);
            $("#newRoomInput").val("");
        }
    }

    //Supprimer la room
    const deleteRoom = (roomId) => {
        sendMessage(
            {
                content: roomId,
                type: 'deleteRoom'
            }, true
        )
        setTimeout(exitRoom, 500);
    }

    //Animer la room
    const animateRoom = (rcvData) => {
        if(globalRoomCode === rcvData.code){
            $("#room").addClass("animate__animated animate__faster animate__" + rcvData.content);
            setTimeout(function (){
                $("#room").removeClass("animate__animated animate__faster animate__" + rcvData.content);
            }, 600);
        }
    }

    //Send drawing
    sendDrawing = () => {
        store[globalRoomCode].paint = JSON.stringify(canvas);
        sendMessage(
            {
                content: JSON.stringify(canvas),
                type: 'canvas'
            }
        )
    }

    $("#clearCanvas").click(function(){
        canvas.clear();
        sendDrawing();

        sendMessage(
            {
                content: " - <i><b>A nettoyé le tableau</b></i>",
                type: 'msg'
            }
        )
    })

    $("#newRoomBtn").click(function(){
        updateState("newRoom", true);
    })

    $("#createNewRoom").click(function(){
        createNewRoom();
    })

    $(".animateRoom").click(function (){
        sendMessage(
            {
                content: this.id,
                type: 'animate'
            }
        )
    })

    //Fonction pour le pluriel des mots
    const verifPluriel = (value) => {
        return (value > 1) ? 's' : '';
    }

    const updateState = (key, val, callback) => {
        //Mise à jour du state
        state[key] = val;

        //Comportement spéciaux de chaque box
        switch (key){
            case 'lobby':
                initLobby();
                break;
            default:
                break;
        }

        //Affichage de la bonne fenêtre
        for (let stateKey in state) {
            //Si la clé dans le state n'existe pas on continue
            if(stateKey === "" || stateKey === undefined){
                continue;
            }
            //On met tout les autres éléments du state à false
            else if(stateKey !== key){
                state[stateKey] = false;
            }
            //Sinon si la valeur de la clé dans le state est vraie on afffiche la box sinon on la cache
            const tempKey = "#" + stateKey;
            (state[stateKey]) ? $(tempKey).show() : $(tempKey).hide();
        }
        if(callback instanceof Function){
            callback();
        }
        //Retour de true
        return true;
    }

    //Si le pseudo est défini on n'affiche pas la pseudoBox et on passe dans le lobby, sinon on l'affiche et plus tard on va dans le lobby
    (pseudo === "" || pseudo === undefined) ? updateState("pseudo", true) : updateState("lobby", true) ;
}

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


    });

    canvas.observe('mouse:up', function () {
        x0 = y0 = 0;
        sendDrawing();
    });



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