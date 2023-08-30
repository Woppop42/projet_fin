function showGameName(name)
{
    let gameName = document.getElementById('gameName');
    gameName.innerText = name;
    gameName.style.visibility = 'visible';

}

function hideGameName()
{
    let gameName = document.getElementById('gameName');
    gameName.style.visibility = 'hidden';

}