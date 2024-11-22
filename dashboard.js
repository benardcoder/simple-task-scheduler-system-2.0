function loadPage(section) {
    const contentArea = document.getElementById("content-area");
    fetch('sections/$ {section}.html')
        .then(response => response.text())
        .then(data => {
            contentArea.innerHTML = data;
            loadSectionScript(section);
        })
        .catch(error => {
            contentArea.innerHTML = <p>Failed to load section: ${error}</p>;
        });
}

function loadSectionScript(section) {
    const scriptTag = document.createElement("script");
    scriptTag.src = scripts/${'section'}.js;
    document.body.appendChild(scriptTag);
}