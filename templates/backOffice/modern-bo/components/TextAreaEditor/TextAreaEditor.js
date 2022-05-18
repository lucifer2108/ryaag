export const TextAreaEditor = () => {
    const button = document.querySelectorAll('button');
    const text = document.getElementsByClassName('textField');
    text.document.designMode = 'On';
    
    for(let i=0; i < button.length; i++) {
      button[i].addEventListener('click', ()=>{
        let cmd = button[i].getAttribute('data-cmd');
        if(button[i].name === 'active') {
          button[i].classList.toggle('active');
        }
        
        if(cmd === 'link') {
          let url = prompt('Veuillez entrer votre lien ici : ', ' ');
          text.document.execCommand(cmd, false, url);
        } else {
          text.document.execCommand(cmd, false, null);
        }
      }
      )
    }
}