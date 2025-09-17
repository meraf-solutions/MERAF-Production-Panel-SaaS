/*********************************/
/*         INDEX                 */
/*================================
 *     01.  Tiny Slider          *
 *     02.  Countdown Js         * (For Comingsoon pages)
 *     03.  Maintenance js       * (For Maintenance page)
 *     04.  Data Counter         *
 *     05.  Gallery filter js    * (For Portfolio pages)
 *     06.  Tobii lightbox       * (For Portfolio pages)
 *     07.  CK Editor            * (For Compose mail)
 *     08.  Validation Form      * 
 *     09.  Switcher Pricing Plan* 
 *     10.  Charts               * 
 *     11.  Connect wallet       * 
 *     12.  Switcher Js          * 
 ================================*/
         
//=========================================//
/*            01) Tiny slider              */
//=========================================//
if(document.getElementsByClassName('tiny-single-item').length > 0) {
    var slider = tns({
        container: '.tiny-single-item',
        items: 1,
        controls: false,
        mouseDrag: true,
        loop: true,
        rewind: true,
        autoplay: true,
        autoplayButtonOutput: false,
        autoplayTimeout: 3000,
        navPosition: "bottom",
        speed: 400,
        gutter: 16,
    });
};

//=========================================//
/*/*            02) Countdown js           */
//=========================================//

try {
    if(document.getElementById("days")){
        // The data/time we want to countdown to
        var eventCountDown = new Date("December 25, 2022 16:37:52").getTime();

        // Run myfunc every second
        var myfunc = setInterval(function () {

            var now = new Date().getTime();
            var timeleft = eventCountDown - now;

            // Calculating the days, hours, minutes and seconds left
            var days = Math.floor(timeleft / (1000 * 60 * 60 * 24));
            var hours = Math.floor((timeleft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((timeleft % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((timeleft % (1000 * 60)) / 1000);

            // Result is output to the specific element
            document.getElementById("days").innerHTML = days + "<p class='count-head'>Days</p> "
            document.getElementById("hours").innerHTML = hours + "<p class='count-head'>Hours</p> "
            document.getElementById("mins").innerHTML = minutes + "<p class='count-head'>Mins</p> "
            document.getElementById("secs").innerHTML = seconds + "<p class='count-head'>Secs</p> "

            // Display the message when countdown is over
            if (timeleft < 0) {
                clearInterval(myfunc);
                document.getElementById("days").innerHTML = ""
                document.getElementById("hours").innerHTML = ""
                document.getElementById("mins").innerHTML = ""
                document.getElementById("secs").innerHTML = ""
                document.getElementById("end").innerHTML = "00:00:00:00";
            }
        }, 1000);
    }
} catch (err) {

}


//=========================================//
/*/*            03) Maintenance js         */
//=========================================//

try {
    if(document.getElementById("maintenance")){
        var seconds = 3599;
        function secondPassed() {
            var minutes = Math.round((seconds - 30) / 60);
            var remainingSeconds = seconds % 60;
            if (remainingSeconds < 10) {
                remainingSeconds = "0" + remainingSeconds;
            }
            document.getElementById('maintenance').innerHTML = minutes + ":" + remainingSeconds;
            if (seconds == 0) {
                clearInterval(countdownTimer);
                document.getElementById('maintenance').innerHTML = "Buzz Buzz";
            } else {
                seconds--;
            }
        }
        var countdownTimer = setInterval('secondPassed()', 1000);
    }
} catch (err) {

}

//=========================================//
/*/*            04) Data Counter           */
//=========================================//

//=========================================//
/*/*            05) Gallery filter js      */
//=========================================//

try {
    var Shuffle = window.Shuffle;

    class Demo {
        constructor(element) {
            if(element){
                this.element = element;
                this.shuffle = new Shuffle(element, {
                    itemSelector: '.picture-item',
                    sizer: element.querySelector('.my-sizer-element'),
                });

                // Log events.
                this.addShuffleEventListeners();
                this._activeFilters = [];
                this.addFilterButtons();
            }
        }

        /**
         * Shuffle uses the CustomEvent constructor to dispatch events. You can listen
         * for them like you normally would (with jQuery for example).
         */
        addShuffleEventListeners() {
            this.shuffle.on(Shuffle.EventType.LAYOUT, (data) => {
                console.log('layout. data:', data);
            });
            this.shuffle.on(Shuffle.EventType.REMOVED, (data) => {
                console.log('removed. data:', data);
            });
        }

        addFilterButtons() {
            const options = document.querySelector('.filter-options');
            if (!options) {
                return;
            }

            const filterButtons = Array.from(options.children);
            const onClick = this._handleFilterClick.bind(this);
            filterButtons.forEach((button) => {
                button.addEventListener('click', onClick, false);
            });
        }

        _handleFilterClick(evt) {
            const btn = evt.currentTarget;
            const isActive = btn.classList.contains('active');
            const btnGroup = btn.getAttribute('data-group');

            this._removeActiveClassFromChildren(btn.parentNode);

            let filterGroup;
            if (isActive) {
                btn.classList.remove('active');
                filterGroup = Shuffle.ALL_ITEMS;
            } else {
                btn.classList.add('active');
                filterGroup = btnGroup;
            }

            this.shuffle.filter(filterGroup);
        }

        _removeActiveClassFromChildren(parent) {
            const { children } = parent;
            for (let i = children.length - 1; i >= 0; i--) {
                children[i].classList.remove('active');
            }
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        window.demo = new Demo(document.getElementById('grid'));
    });
} catch (err) {

}


//=========================================//
/*/*            06) Tobii lightbox         */
//=========================================//

try {
    const tobii = new Tobii()
} catch (err) {

}


//=========================================//
/*/*            07) CK Editor              */
//=========================================//

try {
    ClassicEditor
    .create(document.querySelector('#projectChangelog'))
    .catch(error => {
        console.error(error);
    });
} catch(err) {

}

//=========================================//
/*/*    08) Validation Shop Checkouts      */
//=========================================//

(function () {
    'use strict'

    if(document.getElementsByClassName('needs-validation').length > 0) {
        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        var forms = document.querySelectorAll('.needs-validation')

        // Loop over them and prevent submission
        Array.prototype.slice.call(forms)
            .forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
                }

                form.classList.add('was-validated')
            }, false)
        })
    }
})()


//=========================================//
/*/*      09) Switcher Pricing Plans       */
//=========================================//
try {
    var e = document.getElementById("filt-monthly"),
        d = document.getElementById("filt-yearly"),
        t = document.getElementById("switcher"),
        m = document.getElementById("monthly"),
        y = document.getElementById("yearly");

    e.addEventListener("click", function(){
        t.checked = false;
        e.classList.add("toggler--is-active");
        d.classList.remove("toggler--is-active");
        m.classList.remove("hide");
        y.classList.add("hide");
    });

    d.addEventListener("click", function(){
        t.checked = true;
        d.classList.add("toggler--is-active");
        e.classList.remove("toggler--is-active");
        m.classList.add("hide");
        y.classList.remove("hide");
    });

    t.addEventListener("click", function(){
        d.classList.toggle("toggler--is-active");
        e.classList.toggle("toggler--is-active");
        m.classList.toggle("hide");
        y.classList.toggle("hide");
    })
} catch(err) {

}

//=========================================//
/*/*      10) Charts                       */
//=========================================//

//=========================================//
/*            11) Connect wallet           */
//=========================================//
;(async function () {
    try {
      //Basic Actions Section
      const onboardButton = document.getElementById('connectWallet')
  
      //   metamask modal
      const modal = document.getElementById('modal-metamask')
      const closeModalBtn = document.getElementById('close-modal')
  
      //   wallet address
      const myPublicAddress = document.getElementById('myPublicAddress')
  
      //Created check function to see if the MetaMask extension is installed
      const isMetaMaskInstalled = () => {
        //Have to check the ethereum binding on the window object to see if it's installed
        const {ethereum} = window
        return Boolean(ethereum && ethereum.isMetaMask)
      }
  
      const onClickConnect = async () => {
        if (!isMetaMaskInstalled()) {
          //meta mask not installed
          modal.classList.add('show')
          modal.style.display = 'block'
          return
        }
        try {
          await ethereum.request({method: 'eth_requestAccounts'})
          const accounts = await ethereum.request({method: 'eth_accounts'})
          myPublicAddress.innerHTML =
            accounts[0].split('').slice(0, 6).join('') +
            '...' +
            accounts[0]
              .split('')
              .slice(accounts[0].length - 7, accounts[0].length)
              .join('')
        } catch (error) {
          console.error(error)
        }
      }
  
      const closeModal = () => {
        modal.classList.remove('show')
        modal.style.display = 'none'
      }
  
      if (isMetaMaskInstalled()) {
        const accounts = await ethereum.request({method: 'eth_accounts'})
        if (!!accounts[0]) {
          myPublicAddress.innerHTML =
            accounts[0].split('').slice(0, 6).join('') +
            '...' +
            accounts[0]
              .split('')
              .slice(accounts[0].length - 7, accounts[0].length)
              .join('')
        }
      }
  
      onboardButton.addEventListener('click', onClickConnect)
      closeModalBtn.addEventListener('click', closeModal)
    } catch (error) {}
  })()

//=========================================//
/*            12) Switcher JS              */
//=========================================//

try {
    function setColor(theme) {
        document.getElementById('color-opt').href = './css/colors/' + theme + '.min.css';
        toggleSwitcher(false);
    };
    
    function setTheme(theme) {
        // Create a cookie to store the theme preference
        document.cookie = `theme=${theme}; expires=${new Date(Date.now() + 365 * 24 * 60 * 60 * 1000).toUTCString()}; path=/`;        
        
        let bootstarpHref;
        let styleHref;
		let sidebarClass;
    
        switch (theme) {
            case "style-dark":
                bootstarpHref =  window.location.origin + '/assets/css/bootstrap-dark.min.css';
                styleHref = window.location.origin + '/assets/css/style-dark.min.css';
				sidebarClass = 'sidebar-dark';
                break;
            case "style-rtl":
                bootstarpHref =  window.location.origin + '/assets/css/bootstrap-rtl.min.css';
                styleHref = window.location.origin + '/assets/css/style-rtl.min.css';
				sidebarClass = 'sidebar-light';
                break;
            case "style-dark-rtl":
                bootstarpHref =  window.location.origin + '/assets/css/bootstrap-dark-rtl.min.css';
                styleHref = window.location.origin + '/assets/css/style-dark-rtl.min.css';
				sidebarClass = 'sidebar-dark';
                break;               
            
            default:
                bootstarpHref =  window.location.origin + '/assets/css/bootstrap.min.css';
                styleHref = window.location.origin + '/assets/css/style.min.css';
				sidebarClass = 'sidebar-light';
                break;
        }
    
        // Set the direction of the page
        if (theme === "style-rtl" || theme === "style-dark-rtl") {
            document.getElementsByTagName("html")[0].dir = "rtl";
        } else {
            document.getElementsByTagName("html")[0].dir = "ltr";
        }
    
        // Update the stylesheet links
        document.getElementsByClassName('theme-opt')[0].href = bootstarpHref;
        document.getElementsByClassName('theme-opt')[1].href = styleHref;
    
        // Hide the other button
        const darkButton = document.querySelector('.dark-version');
        const lightButton = document.querySelector('.light-version');
    
        if (theme === "style-dark" || theme === "style-dark-rtl") {
            darkButton.parentNode.style.display = 'none'; // Hides the parent li element
            lightButton.parentNode.style.display = ''; // Resetting to default display
        } else {
            darkButton.parentNode.style.display = '';
            lightButton.parentNode.style.display = 'none';
        }
		
		// Update the sidebar class
		const sidebarElement = document.getElementById('sidebar');
		sidebarElement.classList.remove('sidebar-light');
		sidebarElement.classList.remove('sidebar-dark');
		sidebarElement.classList.add(sidebarClass);
    };    
    
} catch (error) {
    
}