if ('pictureInPictureEnabled' in document) {
    const videoElements = document.querySelectorAll('video');

    window.addEventListener('scroll', () => {
        videoElements.forEach(video => {
            const rect = video.getBoundingClientRect();
            if (rect.top < window.innerHeight) {
                enablePictureInPicture(video);
            }
        });
    });

    function enablePictureInPicture(video) {
        if (document.pictureInPictureElement !== video) {
            video.requestPictureInPicture();
        }
    }

}
