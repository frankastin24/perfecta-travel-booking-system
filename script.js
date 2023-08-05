const generateSelect = (value) => {
  let selectHTML = "<select>";
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
  return selectHTML;
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

  if (first) {
    calendarHTML += generateSelect(month.name);
  } else {
    calendarHTML += "<span>" + month.name + " " + month.year + "</span>";
  }

  calendarHTML += nextArrow + "</header>";

  calendarHTML +=
    '<div class="calendar-body"><div class="day day-name">M</div><div class="day day-name">T</div><div class="day day-name">W</div><div class="day day-name">T</div><div class="day day-name">F</div><div class="day day-name">S</div><div class="day day-name">S</div>';

  month.days.forEach((day) => {
    if (day.class == "active") {
      calendarHTML +=
        '<div data-date="' +
        day.date +
        '" ' +
        'data-duration="' +
        day.duration +
        '" ' +
        'data-num_rooms="' +
        day.num_rooms +
        '" ' +
        'data-price_upper="' +
        day.price_upper +
        '" ' +
        'data-price_lower="' +
        day.price_lower +
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

  const basePrice = parseInt(
    $(".active.selected").data($('input[name="position"]').val())
  );

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
      roomobj.price = basePrice * 1.5;
      fullPrice += basePrice * 1.5;
    }
    if (value == 2) {
      roomobj.price = basePrice;
      fullPrice += basePrice;
    }
    if (value > 2) {
      roomobj.price = basePrice * (parseInt($(el).find("input").val()) - 2);
      fullPrice +=
        basePrice + basePrice * (parseInt($(el).find("input").val()) - 2);
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

  for (let x = index; x < index + 3; x++) {
    var last = x == index + 2;

    calendarsHTML += generateCalendar(x, x == index, last);
  }

  $(".calendar-container").html(calendarsHTML);
};

$ = jQuery;
let isSubmit = false;
let currentCalendar = 0;
$(() => {
  console.log(typeof PoaData);
  if (typeof PoaData == "undefined") {
  } else {
    generateCalendars(0);
  }

  $('input[type="submit"]').on("click", (e) => {
    const valid = validate(true);
    calculatePrice();
    if (!valid) {
      e.preventDefault();
      return;
    }

    if (!isSubmit) {
      e.preventDefault();
      $.ajax({
        type: "POST",
        url: PoaData.ajax_url,
        dataType: "json",
        data: {
          action: "generate_wspay_signature",
          amount: $(".total-amount").html(),
          rooms: roomObjs,
          dates: $(".dates-from-to").html(),
        },

        beforeSend: function () {},
        success: function (data) {
          $('input[name="Signature"]').val(data.Signature);
          $('input[name="ShoppingCartID"]').val(data.ShoppingCardID);
          $('input[name="TotalAmount"]').val($(".total-amount").html());
          isSubmit = true;
          $("form").submit();
        },
      });
    }
  });

  $("body").on("change", "select", (e) => {
    const index = $(e.currentTarget).find(":selected").index();
    currentCalendar = index;
    generateCalendars(index);
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

    const length = index + (parseInt($(e.currentTarget).data("duration")) + 1);

    for (let x = index; x < length; x++) {
      $(e.currentTarget).siblings(".day").eq(x).addClass("hover");
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
    const room_selects = $(".select").filter((index, el) => {
      return (
        $(el).find("input").attr("name").indexOf("room") > -1 &&
        !($(el).find("input").attr("name").indexOf("rooms") > -1)
      );
    });

    room_selects.remove();

    const length = index + (parseInt($(e.currentTarget).data("duration")) + 1);
    if ($(e.currentTarget).hasClass("selected")) {
      $(e.currentTarget).removeClass("selected");
      for (let x = index; x < length; x++) {
        $(".day").eq(x).removeClass("selected");
      }

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
            (y == 0 ? " Person" : " People") +
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

      dates +=
        " " +
        $(e.currentTarget)
          .siblings(".day")
          .eq(length - 1)
          .data("date");

      $(".dates-from-to").html(dates);
      calculatePrice();
      for (let x = index; x < length; x++) {
        $(e.currentTarget).siblings(".day").eq(x).addClass("selected");
      }
    }
  });
  $("body").on("click", ".day.selected", (e) => {
    $(".selected").removeClass("selected");
    $(".dates-from-to").html("");
  });

  if (window.location.href.split("?")[1]) {
    setTimeout(() => {
      $("#place_order").click();
      console.log("here");
    }, 1000);
  }
});
