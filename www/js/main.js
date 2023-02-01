// Creation d'une function qui écoute un clavier et remplace les guillemets anglais par des guillemets français. 

const input = document.querySelectorAll('input');

for (i=0; i<input.length; i++) {
  if(input[i].type=="text") {
    input[i].addEventListener("keyup", function(){
      remplacerGuillemets(this);
    });
  }
}

function remplacerGuillemets(e){
  if(e.classList.contains("exergue")) {
    while((i=e.value.search('"'))>=0) {
      tabCaractere = ["a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z","A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","0","1","2","3","4","5","6","7","8","9",".","!","é","(",")","è","_","ç","à",";",":","ù","*","$","€",",","?","!","“","”"];
      for (i=0; i<tabCaractere.length; i++) {
        e.value=e.value.replace(tabCaractere[i]+'"', tabCaractere[i]+' ”');
      }
      e.value=e.value.replace('"',' “ ');
    }
  } else {
    while((i=e.value.search('"'))>=0) {
      tabCaractere = ["a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z","A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","0","1","2","3","4","5","6","7","8","9",".","!","é","(",")","è","_","ç","à",";",":","ù","*","$","€",",","?","!","“","”"];
      for (i=0; i<tabCaractere.length; i++) {
        e.value=e.value.replace(tabCaractere[i]+'"', tabCaractere[i]+' »');
      }
      e.value=e.value.replace('"',' « ');
    }
  }
}


// Agrandir une image. 

const allImage = document.querySelectorAll('img');
for (let i=0; i<allImage.length; i++) {
  if(!(allImage[i].classList.contains('non-agrandi'))){
    allImage[i].addEventListener("mouseover", function (){
      this.style.cursor="pointer";
    });
    allImage[i].addEventListener("click", function(){
      if(!(this.classList.contains('modal'))) {
        this.requestFullscreen();
      }
    });
  }
}

document.addEventListener("click", function(){
  if (document.fullscreenElement) {
    document.exitFullscreen();
  }
});

// Deflouter photo 

const image = document.querySelectorAll('.modal');
for (let i=0; i<image.length; i++) {
    let divImg = image[i].closest('.divImg');
    let width=image[i].width;
    let height=image[i].height;

    divImg.style.width=width+"px";
    divImg.style.height=height+"px";

    divImg.addEventListener("click", function(){
      this.children[0].classList.remove("modal");
      let p=this.children[1];
      p.classList.add('hidden');
    });
}


// Animation de la page d'accueil. 

const divContenu = document.querySelectorAll(".divContenu");

for (let i=0; i<divContenu.length;i++) {

  divContenu[i].addEventListener("click", function(){
    for (let j=0; j<divContenu.length; j++) {
      if (divContenu[j].classList.contains('d-flex--gap')) {
        divContenu[j].classList.remove("d-flex--gap");
        divContenu[j].children[1].classList.add("hidden");
      }
    }
    this.classList.add("d-flex--gap");
    this.children[1].classList.remove("hidden");
  });

  divContenu[i].addEventListener("mouseover", function (){
    this.style.cursor="pointer";
  });

}