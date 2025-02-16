let body = document.body;
let profile = document.querySelector('header .flex .profile');

document.querySelector('#user-btn').onclick=()=>{
  profile.classList.toggle('active');
}

let searchForm = document.querySelector('.header .flex .search-form');
document.querySelector('#search-btn').onclick=()=>
{
searchForm.classList.toggle('active');
profile.classList.remove('active')

}
let sideBar = document.querySelector('.side-bar');
document.querySelector('#menu-btn').onclick=()=>
{
  sideBar.classList.toggle('active');
  body.classList.toggle('active');

}

window.onscroll = () =>{
  profile.classList.remove('active');
  searchForm.classList.remove('active');

  if(window.innerWidth < 1200)
  {
    sideBar.classList.remove('active');
    body.classList.remove('active');
  }
}


// // Counter 
// (() => {
//  const counter = document.querySelectorAll('.counter');
//  //convert to array
//  const array = Array.from(counter);
//  //select array elemet
//  array.map((item) => {
//   //data layer
//   let counterInnerText = item.textContent;
//   item.textContent = 0;
//   let count =  1;
//   let speed = item.dataset.speed / counterInnerText;
//   function counterUp(){
//     item.textContent = count++;
//     if(counterInnerText < count)
//     {
//       clearInterval(stop);
//     }
//   }
//   const stop = setInterval(() => {
//     counterUp();

//   },speed)

//  })

// })()


    // JavaScript to remove messages automatically after 5 seconds
    document.addEventListener("DOMContentLoaded", function() {
        setTimeout(function() {
            var messages = document.querySelectorAll(".message");
            messages.forEach(function(message) {
                message.style.transition = "opacity 1s ease";
                message.style.opacity = "0";
                setTimeout(function() {
                    message.remove();
                }, 1000); // Wait for 1 second for the fade-out transition
            });
        }, 5000); // 5 seconds delay
    });
