import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    const carousels = document.querySelectorAll('.luxury-carousel');

    carousels.forEach((carousel) => {
        const inner = carousel.querySelector('.carousel-inner');
        const items = inner ? Array.from(inner.querySelectorAll('.carousel-item')) : [];

        if (!inner || items.length === 0) {
            return;
        }

        const visibleCount = 5;

        if (items.length < visibleCount * 2) {
            const clonesNeeded = visibleCount * 2 - items.length;
            for (let i = 0; i < clonesNeeded; i += 1) {
                const clone = items[i % items.length].cloneNode(true);
                clone.classList.add('carousel-item-clone');
                inner.appendChild(clone);
            }
        }

        const slides = Array.from(inner.querySelectorAll('.carousel-item'));

        inner.style.display = 'flex';
        inner.style.gap = '1rem';
        inner.style.willChange = 'transform';

        slides.forEach((slide) => {
            slide.style.display = 'block';
            slide.style.flex = `0 0 calc((100% - ${4 * 1}rem) / ${visibleCount})`;
            slide.style.maxWidth = `calc((100% - ${4 * 1}rem) / ${visibleCount})`;
        });

        let index = 0;
        let paused = false;
        let slideWidth = 0;

        const recalc = () => {
            const first = inner.querySelector('.carousel-item');
            slideWidth = first ? first.getBoundingClientRect().width + 16 : 0;
        };

        const goTo = (nextIndex, animate = true) => {
            if (animate) {
                inner.style.transition = 'transform 600ms ease';
            } else {
                inner.style.transition = 'none';
            }

            inner.style.transform = `translateX(${-nextIndex * slideWidth}px)`;
        };

        const advance = () => {
            if (paused || !slideWidth) {
                return;
            }

            index += 1;
            goTo(index, true);

            if (index >= slides.length - visibleCount) {
                window.setTimeout(() => {
                    index = 0;
                    goTo(index, false);
                }, 650);
            }
        };

        recalc();
        goTo(0, false);

        const interval = window.setInterval(advance, 2200);

        carousel.addEventListener('mouseenter', () => {
            paused = true;
        });

        carousel.addEventListener('mouseleave', () => {
            paused = false;
        });

        window.addEventListener('resize', () => {
            recalc();
            goTo(index, false);
        });

        carousel.addEventListener('remove', () => {
            window.clearInterval(interval);
        });
    });
});
