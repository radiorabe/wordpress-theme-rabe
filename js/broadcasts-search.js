var searchStyle = document.getElementById('searchstyle');
document.getElementById('broadcasts-search').addEventListener('input', function() {
	if (!this.value) {
		searchStyle.innerHTML = "";
		return;
	}
		searchStyle.innerHTML = ".cat-item:not([data-index*=\"" + this.value.toLowerCase() + "\"]) { display: none; }";
});
