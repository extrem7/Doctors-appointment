function GET() {
    var query = location.search.substr(1);
    var result = {};
    query.split("&").forEach(function (part) {
        var item = part.split("=");
        result[item[0]] = decodeURIComponent(item[1]);
    });
    return result;
}

class Doctors_appointment {
    constructor() {
        this.controller()
    }

    sendForm(e) {
        e.preventDefault();
        let formData = $(e.currentTarget).serialize();
        $('#modal-success').modal({closeExisting: true});
        $.post('/', formData, function (data) {
            console.log(data);
            setTimeout(() => {
                $.modal.close();
            }, 4000);
        }).done(response => {
            console.log('Success');
        }).fail(response => {
            console.log(response)
            console.log('Ajax failed');
        });
    }

    carouselButton(e) {
        e.preventDefault();
        const doctorSlug = $(e.currentTarget).attr('data-doctor');
        $('#modal-form select[name=doctor]').val(doctorSlug);
        $('#modal-form').modal();
    }

    controller() {
        $('.da-form ').submit(e => this.sendForm(e));
        $('.doctors-button').click(e => this.carouselButton(e));
        if (GET().thanks == 1) {
            setTimeout(() => {
                $('#modal-comment').modal();
                setTimeout(() => {
                    $.modal.close();
                }, 4000);
            }, 1500);
            window.history.replaceState(null, null, window.location.pathname);
        }
    }
}

$(() => {
    new Doctors_appointment();
//jquery plugins init and config
    $('input[type=tel]').mask("+38 (999) 999-9999");
    $('#doctors-slider').slick({
        autoplay: true,
        dots: true,
        slide: '.doctors-carousel-item',
        slidesToShow: 4,
        slidesToScroll: 1,
        mobileFirst: false,
        responsive: [
            {
                breakpoint: 1024,
                settings: {
                    slidesToShow: 4
                }
            },
            {
                breakpoint: 768,
                settings: {
                    slidesToShow: 3
                }
            },
            {
                breakpoint: 640,
                settings: {
                    slidesToShow: 2
                }
            },
            {
                breakpoint: 480,
                settings: {
                    slidesToShow: 1,
                    adaptiveHeight: true
                }
            }
        ]
    });
    $.modal.defaults.fadeDuration = 500;
    $.modal.defaults.fadeDelay = 0.50;
});