
var creative = {};
var car = {};
/**
 * Window onload handler.
 */
function preInit() {
  setupDom();

  if (Enabler.isInitialized()) {
    init();
  } else {
    Enabler.addEventListener(
      studio.events.StudioEvent.INIT,
      init
    );
  }
}

/**
 * Select ad elements with zepto selectors to setup dom
 */
function setupDom() {
  creative.dom = {};
  creative.dom.mainContainer = document.getElementById('main-container');
  creative.dom.exit = document.getElementById('exit');


 creative.dom.copy1 = $('#copy1');
 creative.dom.copy2 = $('#copy2');
 creative.dom.copy3 = $('#copy3');

 creative.dom.imagesCont = $('#images-container');
 creative.dom.rack = $('#rack');
 creative.dom.shirt = $('#shirt');
 creative.dom.man1 = $('#mannequin1');
 creative.dom.man2 = $('#mannequin2');

  creative.dom.logo = $('#logo');
  creative.dom.cta = $('#cta');

  creative.dom.startNowButtonShimmer = $('.icon-effect');

}

/**
 * Ad initialisation.
 */
function init() {

  addListeners();

  // Polite loading
  if (Enabler.isVisible()) {
    show();
  }
  else {
    Enabler.addEventListener(studio.events.StudioEvent.VISIBLE, show);
  }
}

/**
 * Adds appropriate listeners at initialization time
 */
function addListeners() {
  creative.dom.exit.addEventListener('click', exitClickHandler);
}

/**
 *  Shows the ad.
 */
function show() {
  creative.dom.exit.style.display = "block";
    //run the animations

  runAnimationLoop();
}






function runAnimationLoop(){
  creative.MainTimeline = new TimelineLite({
    onComplete:repeatMainTimeline
  });

  creative.MainTimeline


    .addLabel('frame-1')
    .to(creative.dom.copy1, 0.5, {left:0, ease:Circ.easeOut, delay:0.5}, 'frame-1')

    .addLabel('frame-2')
    .to(creative.dom.copy1, 0.5, {autoAlpha:0, delay:3.5}, 'frame-2')
    .to(creative.dom.rack, 0.5, {left:114, ease:Circ.easeOut, delay:3.5}, 'frame-2')


    .addLabel('frame-3')
    .to(creative.dom.rack, 1, {autoAlpha:0, delay:1.25}, 'frame-3')
    .to(creative.dom.shirt, 1, {autoAlpha:1, delay:1.25}, 'frame-3')

    .to(creative.dom.man1, 1, {autoAlpha:1, delay:2.25}, 'frame-3')
    .to(creative.dom.shirt, 0.5, {autoAlpha:0, delay:2.75}, 'frame-3')


    .addLabel('frame-4')
    .to(creative.dom.man1, 1, {scale:0.45, top:90, left:57, autoAlpha:0, ease:Circ.easeOut, delay:0}, 'frame-4')
    .to(creative.dom.copy3, 0.5, {autoAlpha:1, delay:1}, 'frame-4')
    .to(creative.dom.man2, 1, {autoAlpha:1, ease:Circ.easeOut, delay:0}, 'frame-4')

    .addLabel('frame-5')
    .to(creative.dom.copy3, 0.5, {top:95, delay:2}, 'frame-5')
    .to(creative.dom.man2, 0.5, {autoAlpha:0,top:180, ease:Circ.easeOut, delay:2}, 'frame-5')
    .to(creative.dom.logo, 0.25, {scale:1, top:300, ease:Circ.easeIn, delay:2}, 'frame-5')
    .set(creative.dom.logo, {top:-100, delay:2.5}, 'frame-5')
    .to(creative.dom.logo, 0.5, {top:54, ease:Circ.easeOut, delay:2.75}, 'frame-5')
    .to(creative.dom.cta, 0.5, {scale:1, autoAlpha:1, ease:Circ.easeOut, delay:3.25}, 'frame-5')



    .addLabel('shimmer')
 .set(creative.dom.startNowButtonShimmer, {className: "+=shimmer"},'shimmer')
 .to(creative.dom.startNowButtonShimmer,0.6, {autoAlpha:0,delay:0.5},'shimmer')


 creative.playedOnce = false;
 function repeatMainTimeline(){
   if (creative.playedOnce == false) {
     creative.playedOnce = true;
     setTimeout(function(){
       creative.MainTimeline.restart();
     },3000)
   } else {
     //donothing
   }
 }

}


// ---------------------------------------------------------------------------------
// MAIN
// ---------------------------------------------------------------------------------

function exitClickHandler() {
  Enabler.exit('BackgroundExit');
}



/**
 *  Main onload handler
 */
window.addEventListener('load', preInit);
