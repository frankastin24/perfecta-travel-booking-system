let singlePerson = false;
let basePrice = null;
const generateSelect = (value) => {
  let selectHTML =
    "<select><option value='null' >Please Select A Month</option>";

  PoaData["months"].forEach((month) => {
    selectHTML +=
      "<option " +
      (value == month.name ? 'selected="selected"' : "") +
      '  value="' +
      month.name +
      '">' +
      month.name +
      " " +
      month.year +
      "</option>";
  });

  selectHTML += "</select>";
  $(".calendar-container").html(selectHTML + '<div class="months"></div>');
};
let roomObjs = [];
const generateCalendar = (index, first, last) => {
  if (!PoaData.months[index]) return "";

  let month = PoaData.months[index];

  const prevArrow =
    first && PoaData.months[index - 1]
      ? '<button class="prev"><div ></div></button>'
      : "";
  const nextArrow =
    last && PoaData.months[index + 1]
      ? '<button class="next"><div ></div></button>'
      : "";

  let calendarHTML =
    `<div class="poa-booking-calendar">
  <header>` + prevArrow;

  calendarHTML += "<span>" + month.name + " " + month.year + "</span>";

  calendarHTML += nextArrow + "</header>";

  calendarHTML +=
    '<div class="calendar-body"><div class="day day-name">M</div><div class="day day-name">T</div><div class="day day-name">W</div><div class="day day-name">T</div><div class="day day-name">F</div><div class="day day-name">S</div><div class="day day-name">S</div>';

  month.days.forEach((day) => {
    if (day.class == "active month-day") {
      calendarHTML +=
        '<div data-date="' +
        day.date +
        '" ' +
        'data-enddate="' +
        day.enddate +
        '" ' +
        'data-duration="' +
        day.duration +
        '" ' +
        'data-num_rooms="' +
        day.num_rooms +
        '" ';

      if (day.price_upper) {
        calendarHTML +=
          'data-price_upper="' +
          day.price_upper +
          '" ' +
          'data-price_lower="' +
          day.price_lower;
      } else {
        calendarHTML +=
          'data-price_per_person="' +
          day.price_per_person +
          '" ' +
          'data-single_person_supplement="' +
          day.single_person_supplement;
      }

      calendarHTML +=
        '" ' +
        'data-number_rooms="' +
        day.number_rooms +
        '" ' +
        'data-people_per_room="' +
        day.people_per_room +
        '" ' +
        'class="day ' +
        day.class +
        '">' +
        day.number +
        "</div>";
    } else {
      calendarHTML +=
        '<div data-date="' +
        day.date +
        '" ' +
        'class="day ' +
        day.class +
        '">' +
        day.number +
        "</div>";
    }
  });
  calendarHTML += "</div></div>";
  return calendarHTML;
};

const calculatePrice = () => {
  if (!validate()) return;

  if (PoaData.package_type != "ordinary") {
    basePrice = parseInt(
      $(".active.selected").data($('input[name="position"]').val())
    );
  }

  let fullPrice = 0;

  const room_selects = $(".select").filter((index, el) => {
    return (
      $(el).find("input").attr("name").indexOf("room") > -1 &&
      !($(el).find("input").attr("name").indexOf("rooms") > -1)
    );
  });

  const visible_room_selects = room_selects.filter(":visible");
  roomObjs = [];
  visible_room_selects.each((index, el) => {
    if ($(el).find("input").val() == "") return;

    const value = $(el).find("input").val();

    let roomobj = { number_of_people: value };

    if (value == 1) {
      if (singlePerson) {
        roomobj.price = basePrice + parseInt(singlePerson);
        fullPrice += basePrice + parseInt(singlePerson);
      } else {
        roomobj.price = basePrice * 1.5;
        fullPrice += basePrice * 1.5;
      }
    }

    if (value > 1) {
      roomobj.price = basePrice * parseInt($(el).find("input").val());
      fullPrice += basePrice * parseInt($(el).find("input").val());
    }

    roomObjs.push(roomobj);
  });
  $(".total-amount").html(fullPrice);
};

const validate = (displayErrors = false) => {
  let valid = true;
  let calendarValid = true;
  if ($(".dates-from-to").html() == "") {
    valid = false;
    calendarValid = false;
    if (displayErrors == true || displayErrors == "show_calendar_error") {
      $(".calendar-validation-error").show();
    }
  }
  if ($('input[name="position"]').val() == "") {
    valid = false;
    if (displayErrors == true) {
      $('input[name="position"]').siblings("p").show();
    }
  }
  if ($('input[name="num_rooms"]').val() == "") {
    valid = false;
    if (displayErrors == true) {
      $('input[name="num_rooms"]').siblings("p").show();
    }
  }
  if ($('input[name="room_1"]').val() == "") {
    valid = false;
    if (displayErrors == true) {
      $('input[name="room_1"]').siblings("p").show();
    }
  }
  return displayErrors == "show_calendar_error" && calendarValid ? true : valid;
};

const generateCalendars = (index) => {
  let calendarsHTML = "";

  let numCalendars = 1;

  PoaData.months[index].days.forEach((day) => {
    if (day.class == "active month-day") {
      if (day.enddate.split("-")[1] != day.date.split("-")[1]) {
        numCalendars = 2;
      }
    }
  });

  for (let x = index; x < index + numCalendars; x++) {
    var last = x == index + 1;

    calendarsHTML += generateCalendar(x, x == index, last);
  }

  $(".calendar-container .months").html(calendarsHTML);
};

const removeCalendars = (index) => {
  let calendarsHTML = "";

  $(".calendar-container .months").html(calendarsHTML);
};

$ = jQuery;
let isSubmit = false;
let currentCalendar = 0;
$(window).on("resize", () => {
  if ($(".place-order").length > 0) {
    const ws_pay_btn_top =
      $(".place-order").offset().top + $(".place-order").height();

    $('#ws-payment-form input[type="submit"]').css({
      top: ws_pay_btn_top + "px",
      width: $(".place-order").outerWidth() + "px",
    });
  }
});
$(() => {
  if (PoaData.package_type == "ordinary") {
    $(".select").eq(0).remove();
  }
  $('a[href="#book"]').on("click", () => {
    const booking_top = $(".poa-booking").offset().top;
    window.scrollTo(0, booking_top - 150);
  });
  $('form input[type="submit"]').on("click", (e) => {
    const valid = validate(true);
    calculatePrice();
    if (!valid && typeof PoaData != "undefined") {
      e.preventDefault();
      return;
    }

    if (!isSubmit && typeof PoaData != "undefined") {
      e.preventDefault();
      $.ajax({
        type: "POST",
        url: PoaData.ajax_url,
        dataType: "json",
        data: {
          action: "generate_wspay_signature",
          amount: $(".total-amount").html(),
          rooms: roomObjs,
          cruise: $("#trip-name").html(),
          dates: $(".dates-from-to").html(),
        },

        beforeSend: function () {},
        success: function (data) {
          isSubmit = true;
          $(".poa-booking form").submit();
        },
      });
    }
  });
  if ($(".place-order").length > 0) {
    const ws_pay_btn_top =
      $(".place-order").offset().top + $(".place-order").height() + 30;

    $('#ws-payment-form input[type="submit"]').css({
      top: ws_pay_btn_top + "px",
      width: $(".place-order").outerWidth() + "px",
    });
  }

  $('#ws-payment-form input[type="submit"]').on("click", (e) => {
    let isValid = true;
    e.preventDefault();

    if (!isSubmit) {
      $(".woocommerce-billing-fields__field-wrapper  input").each(
        (index, el) => {
          console.log(el);
          if ($(el).val().trim() == "") {
            console.log($(el).attr("name"));
            if ($(el).attr("name") == "CustomerCountry") {
              $(el).val("Croatia");
            } else {
              isValid = false;
            }
          }
        }
      );

      if (!isValid) {
        alert("Please fill in all of the required fields");
      } else {
        $('input[name="CustomerFirstName"]').val(
          $('input[name="billing_first_name"]').val()
        );
        $('input[name="CustomerLastName"]').val(
          $('input[name="billing_last_name"]').val()
        );
        $('input[name="CustomerEmail"]').val(
          $('input[name="billing_email"]').val()
        );
      }
      isSubmit = true;
      $("#ws-payment-form").submit();
    }
  });
  if (typeof PoaData == "undefined") {
  } else {
    generateSelect(null);
  }

  $("body").on("change", "select", (e) => {
    const index = $(e.currentTarget).find(":selected").index();
    currentCalendar = index;

    if (index == 0) {
      removeCalendars();
    } else {
      generateCalendars(index - 1);
    }
  });

  $("body").on("click", ".prev", (e) => {
    currentCalendar--;
    generateCalendars(currentCalendar);
  });

  $("body").on("click", ".next", (e) => {
    currentCalendar++;
    generateCalendars(currentCalendar);
  });

  $("body").on("mouseover", ".day.active", (e) => {
    const index = $(e.currentTarget).index();

    $(e.currentTarget).addClass("hover");

    const duration = parseInt($(e.currentTarget).data("duration"));

    const length = index + duration;

    const firstDayOfMonthIndex = $(".month-day").eq(0).index();

    const number_of_days_in_month = $(e.currentTarget)
      .parent()
      .find(".month-day").length;

    if (duration + (index - firstDayOfMonthIndex) > number_of_days_in_month) {
      var length1 =
        duration - (number_of_days_in_month - (index - firstDayOfMonthIndex));

      for (
        let x = index;
        x <
        index + (number_of_days_in_month - (index - firstDayOfMonthIndex)) - 1;
        x++
      ) {
        $(e.currentTarget).siblings(".day").eq(x).addClass("hover");
      }

      const firstDayOfNextMonthIndex = $(e.currentTarget)
        .parent()
        .parent()
        .next()
        .find(".month-day")
        .eq(0)
        .index();

      for (let x = 0; x < length1 + 1; x++) {
        $(e.currentTarget)
          .parent()
          .parent()
          .next()
          .find(".month-day")
          .eq(x)
          .addClass("hover");
      }
    } else {
      for (let x = index; x < length; x++) {
        $(e.currentTarget).siblings(".day").eq(x).addClass("hover");
      }
    }
  });
  $("body").on("mouseout", ".active", (e) => {
    $(".hover").removeClass("hover");
  });
  $("body").on("click", ".select button", (e) => {
    if (!validate("show_calendar_error")) return;

    $(e.currentTarget).parent().toggleClass("active");
  });

  $('input[name="num_rooms"]').on("input", (e) => {
    var room_selects = $(".select").filter((index, el) => {
      return (
        $(el).find("input").attr("name").indexOf("room") > -1 &&
        !($(el).find("input").attr("name").indexOf("rooms") > -1)
      );
    });
    calculatePrice();
    room_selects.hide();
    for (let i = 0; i < parseInt(e.currentTarget.value); i++) {
      room_selects.eq(i).show();
    }
  });
  $("body").on("click", ".select li", (e) => {
    $(".select .selected").removeClass("selected");
    $();
    $(e.currentTarget)
      .addClass("selected")
      .parent()
      .parent()
      .removeClass("active");
    $(e.currentTarget)
      .parent()
      .siblings("button")
      .html($(e.currentTarget).html() + "<span>");
    $(e.currentTarget)
      .parent()
      .siblings("input")
      .val($(e.currentTarget).data("value"))
      .trigger("input");
    $(e.currentTarget).parent().siblings("p").hide();

    calculatePrice();
  });

  $("body").on("click", ".day.active", (e) => {
    const index = $(e.currentTarget).index();

    $(".total-amount").html("0.00");

    $(".select .selected").removeClass("selected");

    $(".select button").html("<span>");

    $(".calendar-validation-error").hide();

    basePrice = $(e.currentTarget).data("price_per_person");
    singlePerson = $(e.currentTarget).data("single_person_supplement");

    const room_selects = $(".select").filter((index, el) => {
      return (
        $(el).find("input").attr("name").indexOf("room") > -1 &&
        !($(el).find("input").attr("name").indexOf("rooms") > -1)
      );
    });

    room_selects.remove();

    const duration = parseInt($(e.currentTarget).data("duration"));

    const length = index + duration;

    const firstDayOfMonthIndex = $(".month-day").eq(0).index();

    const number_of_days_in_month = $(e.currentTarget)
      .parent()
      .find(".month-day").length;

    if ($(e.currentTarget).hasClass("selected")) {
      $(".selected").removeClass("selected");

      $(".dates-from-to").html("");
    } else {
      $(".selected").removeClass("selected");

      $(".calendar .validation-error-message").hide();

      $(e.currentTarget).addClass("selected");

      let dates = $(e.currentTarget).data("date");

      const num_rooms = parseInt($(e.currentTarget).data("number_rooms"));

      const people_per_room = parseInt(
        $(e.currentTarget).data("people_per_room")
      );

      let prevRoom = false;

      for (let x = 0; x < num_rooms; x++) {
        let room_html = "";
        room_html += `<div class="select" style="display: none;">
          <label>Room ${x + 1}</label>
          <input name="room_${x + 1}" type="hidden" value="">
          <button><span></span></button>
          <ul class="options">`;

        for (let y = 0; y < people_per_room; y++) {
          room_html +=
            '<li data-value="' +
            (y + 1) +
            '">' +
            (y + 1) +
            (y == 0 ? " Person" : " Persons") +
            "</li>";
        }

        room_html += `</ul>
            <p class="validation-error-message" style="display: none;">You must select a number of people.</p>
          </div>`;
        if (x == 0) {
          prevRoom = $(room_html);
          $('input[name="num_rooms"]').parent().after(prevRoom);
        } else {
          const newRoom = $(room_html);
          prevRoom.after(newRoom);
          prevRoom = newRoom;
        }
      }

      let num_rooms_html = "";
      for (let x = 1; x < num_rooms + 1; x++) {
        num_rooms_html += '<li data-value="' + x + '">' + x + "</li>";
      }

      $('input[name="num_rooms"]').siblings("ul").html(num_rooms_html);

      dates += " " + $(e.currentTarget).data("enddate");

      $(".dates-from-to").html(dates);
      calculatePrice();

      if (duration + (index - firstDayOfMonthIndex) > number_of_days_in_month) {
        var length1 =
          duration - (number_of_days_in_month - (index - firstDayOfMonthIndex));

        for (
          let x = index;
          x <
          index +
            (number_of_days_in_month - (index - firstDayOfMonthIndex)) -
            1;
          x++
        ) {
          $(e.currentTarget).siblings(".day").eq(x).addClass("selected");
        }
        const firstDayOfNextMonthIndex = $(e.currentTarget)
          .parent()
          .parent()
          .next()
          .find(".month-day")
          .eq(0)
          .index();

        for (let x = 0; x < length1 + 1; x++) {
          $(e.currentTarget)
            .parent()
            .parent()
            .next()
            .find(".month-day")
            .eq(x)
            .addClass("selected");
        }
      } else {
        for (let x = index; x < length; x++) {
          $(e.currentTarget).siblings(".day").eq(x).addClass("selected");
        }
      }
    }
  });
  $("body").on("click", ".day.selected", (e) => {
    $(".selected").removeClass("selected");
    $(".dates-from-to").html("");
  });

  if (window.location.href.split("?")[1]) {
    setTimeout(() => {
      const vars = window.location.href.split("?")[1].split("&");
      vars.forEach((vars) => {
        const name = vars.split("=")[0];
        const value = decodeURI(vars.split("=")[1]);

        switch (name) {
          case "CustomerFirstname":
            console.log($('input[name="billing_first_name"]'));
            $('input[name="billing_first_name"]').val(value);
            break;
          case "CustomerSurname":
            console.log($('input[name="billing_first_name"]'));

            $('input[name="billing_last_name"]').val(value);
            break;
          case "CustomerEmail":
            $('input[name="billing_email"]').val(value);
            break;
          case "CustomerAddress":
            $('input[name="billing_address_1"]').val(value);
            break;
          case "CustomerPhone":
            break;
          case "CustomerCity":
            $('input[name="billing_city"]').val(value);
            break;
          case "CustomerZIP":
            $('input[name="billing_postcode"]').val(value);
            break;
          case "CustomerCountry":
            $('input[name="billing_country"]').val("Croatia");
            break;
        }
      });

      $("#place_order").click();
    }, 1000);
  }
});
