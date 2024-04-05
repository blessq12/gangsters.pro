let mobileMenu = document.querySelector('.mobile-menu')

let mobileMenuObserver = new MutationObserver(function (entries, observer) {
    entries.forEach(e => {
        if (e.target.classList.contains('show')) {
            document.body.classList.add('overflow-hidden')
        } else {
            document.body.classList.remove('overflow-hidden')
        }
    } )
})

mobileMenuObserver.observe(mobileMenu, { attributes: true })

if (window.navigator.standalone !== void(0)) {
	$(window).load(function() {
		if (!this.navigator.standalone) {
			this.setTimeout(function() { this.scrollTo(this.scrollX, this.scrollY + 1); }, 500);
		}
	});
}