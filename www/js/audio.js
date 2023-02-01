const audioPlayers = document.querySelectorAll('audio');

audioPlayers.forEach(audioPlayer => {
  const playButton = audioPlayer.nextElementSibling;
  playButton.addEventListener('click', () => {
    audioPlayer.play();
  });

  const pauseButton = playButton.nextElementSibling;
  pauseButton.addEventListener('click', () => {
    audioPlayer.pause();
  });

  const stopButton = pauseButton.nextElementSibling;
  stopButton.addEventListener('click', () => {
    audioPlayer.pause();
    audioPlayer.currentTime = 0;
  });

  const progressBar = stopButton.nextElementSibling;
  progressBar.addEventListener('click', event => {
    const progress = event.offsetX / event.target.offsetWidth;
    audioPlayer.currentTime = progress * audioPlayer.duration;
  });
  audioPlayer.addEventListener('timeupdate', () => {
    const progress = audioPlayer.currentTime / audioPlayer.duration;
    progressBar.value = progress;

    const elapsedTime = formatTime(audioPlayer.currentTime);
    const totalTime = formatTime(audioPlayer.duration);
    progressBar.nextElementSibling.innerHTML = `${elapsedTime} / ${totalTime}`;
  });
});

function formatTime(time) {
  const minutes = Math.floor(time / 60);
  const seconds = Math.floor(time % 60);
  return `${minutes}:${seconds.toString().padStart(2, '0')}`;
}
