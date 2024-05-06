(function ($) {
  "use strict";

  /** Run functions when document is ready */
  $(document).ready(function () {
    /** Changing calendar month event listener*/
    calendarMonthChangeEventListener();

    /** Changing calendar year event listener*/
    calendarYearChangeEventListener();

    /** Calendar event day button event listener */
    calendarEventDayEventListener();

    /** Show page pagination */
    pagination();

    /** Ad event to calendar event listener*/
    addEventToCalendarEventListener();
  });

  /** Calendar event day button event listener */
  function calendarEventDayEventListener() {
    var $calendarEventDay = $(".event-day-btn");
    if ($calendarEventDay.length > 0) {
      $calendarEventDay.click(function (e) {
        /** Get data-item-key from event day in calendar */
        var dataItemKey = $(this).data("item-key");
        chooseEventDay(e, dataItemKey);
        //Choose all events day
        chooseAllEventDay(e, dataItemKey);
      
       
        handleScreenWidthChange(e);
      });
    }
  }

  /** Changing calendar month event listener*/
  function calendarMonthChangeEventListener() {
    var $calendarMonth = $("#calendar-month");
    if ($calendarMonth.length > 0) {
      $calendarMonth.change(function (e) {
        changeMonthYear(e);
      });
    }
  }

  /** Changing calendar year event listener*/
  function calendarYearChangeEventListener() {
    var $calendarYear = $("#calendar-year");
    if ($calendarYear.length > 0) {
      $calendarYear.change(function (e) {
        changeMonthYear(e);
      });
    }
  }

  /** Ad event to calendar event listener*/
  function addEventToCalendarEventListener() {
    var $toCalendar = $("#to-calendar");
    if ($toCalendar.length > 0) {
      $toCalendar.click(function (e) {
        e.preventDefault(); // Prevent the default behavior of the anchor element
        addEventToCalendar();
      });
    }
  }

  //month selector left
  var $monthLeft = $("#month-btn-left");
  if ($monthLeft.length > 0) {
    $monthLeft.click(function (e) {
      e.preventDefault(); // Prevent the default behavior of the anchor element
      monthswich("left");
      changeMonthYear(e);
    });
  }

  //month selector left
  var $monthRight = $("#month-btn-right");
  if ($monthRight.length > 0) {
    $monthRight.click(function (e) {
      e.preventDefault(); // Prevent the default behavior of the anchor element
      monthswich("right");
      changeMonthYear(e);
    });
  }

  // switch months
  function monthswich(direction) {
    var currentMonthDiv = document.querySelector(".current-month");
    var currentYear = parseInt(
      document.querySelector(".calendar-year").innerHTML
    );

    if (direction == "right") {
      var nextDiv = currentMonthDiv.nextElementSibling;
      if (nextDiv) {
        // Remove the current-month class from the current div
        currentMonthDiv.classList.add("month-hide");
        currentMonthDiv.classList.remove("current-month");

        // Add the current-month class to the next div
        nextDiv.classList.remove("month-hide");
        nextDiv.classList.add("current-month");
      } else {
        // If there's no next div, we're at the last month, so go to the first
        var firstDiv = currentMonthDiv.parentNode.firstElementChild;
        currentMonthDiv.classList.add("month-hide");
        currentMonthDiv.classList.remove("current-month");
        firstDiv.classList.remove("month-hide");
        firstDiv.classList.add("current-month");

        //years counting
        currentYear++;
        document.querySelector(".calendar-year").innerHTML = currentYear;
      }
    } else if (direction == "left") {
      var prevDiv = currentMonthDiv.previousElementSibling;
      if (prevDiv) {
        // Remove the current-month class from the current div
        currentMonthDiv.classList.add("month-hide");
        currentMonthDiv.classList.remove("current-month");

        // Add the current-month class to the previous div
        prevDiv.classList.remove("month-hide");
        prevDiv.classList.add("current-month");
      } else {
        // If there's no previous div, we're at the first month, so go to the last
        var lastDiv = currentMonthDiv.parentNode.lastElementChild;
        currentMonthDiv.classList.add("month-hide");
        currentMonthDiv.classList.remove("current-month");
        lastDiv.classList.remove("month-hide");
        lastDiv.classList.add("current-month");

        //years counting
        currentYear--;
        document.querySelector(".calendar-year").innerHTML = currentYear;
      }
    }
  }

  /** Changing year and month ajax function*/
  function changeMonthYear(e) {
    e.preventDefault();
    // var $month = $("#calendar-month").val();
    // var $year = $("#calendar-year").val();
    var $month = $("#calendar-month").find(".current-month").attr("value");
    var $year = $("#calendar-year").text();

    //Changing to int
    var $month = parseInt($month, 10);
    var $year = parseInt($year, 10);

    $.post(
      ajax_object.ajax_url,
      {
        action: "showCalendar_ajax",
        calendarMonth: $month,
        calendarYear: $year,
        security: ajax_object.nonce,
      },
      function (response) {
        // Get a reference to the div element
        var myDiv = document.getElementById("events-calendar-calendar");

        // Set the value of the div
        myDiv.innerHTML = response;

        // /** Event day event listener */
        calendarEventDayEventListener();

        //Readding event listeners
        //month selector left
        var $monthLeft = $("#month-btn-left");
        if ($monthLeft.length > 0) {
          $monthLeft.click(function (e) {
            e.preventDefault(); // Prevent the default behavior of the anchor element
            monthswich("left");
            changeMonthYear(e);
          });
        }
        //month selector left
        var $monthRight = $("#month-btn-right");
        if ($monthRight.length > 0) {
          $monthRight.click(function (e) {
            e.preventDefault(); // Prevent the default behavior of the anchor element
            monthswich("right");
            changeMonthYear(e);
          });
        }
      }
    );
  }

  /** Choosing event day ajax function*/
  function chooseEventDay(e, dataItemKey) {
    var $eventDate = dataItemKey;

    $.post(
      ajax_object.ajax_url,
      {
        action: "showPost_ajax",
        eventDate: $eventDate,
        security: ajax_object.nonce,
      },
      function (response) {
        // Get a reference to the div element
        var MyDiv = document.getElementById("events-calendar-posts");

        // Set the value of the div
        MyDiv.innerHTML = response;
        // Show posts based on the screen width
        handleScreenWidthChange();
      }
    );
  }

  /** Choosing event day for all events ajax function*/
  function chooseAllEventDay(e, dataItemKey) {
    var $eventDate = dataItemKey;

    $.post(
      ajax_object.ajax_url,
      {
        action: "showAllPost_ajax",
        eventDate: $eventDate,
        security: ajax_object.nonce,
      },
      function (response) {
        // Get a reference to the div element
        var MyDiv = document.getElementById("events-calendar-posts-All");

        // Cancel if block not finded
        if (MyDiv === null) {
          return;
        }
        // Set the value of the div
        MyDiv.innerHTML = response;

        // Refresh pagination after changing calendar day
        pagination();
      }
    );
  }

  

  

 
})(jQuery);

/** Show posts based on the screen width */
/** Show calendar when increasing screen size */
function handleScreenWidthChange() {
  var screenWidth =
    window.innerWidth ||
    document.documentElement.clientWidth ||
    document.body.clientWidth;

  if (screenWidth < 1610) {
    if (document.getElementById("post-5") !== null) {
      document.getElementById("post-5").style.display = "none";
    }

    if (document.getElementById("post-6") !== null) {
      document.getElementById("post-6").style.display = "none";
    }
  }

  if (screenWidth < 1200) {
    if (document.getElementById("post-3") !== null) {
      document.getElementById("post-3").style.display = "none";
    }

    if (document.getElementById("post-4") !== null) {
      document.getElementById("post-4").style.display = "none";
    }

    if (document.getElementById("post-5") !== null) {
      document.getElementById("post-5").style.display = "none";
    }

    if (document.getElementById("post-6") !== null) {
      document.getElementById("post-6").style.display = "none";
    }
  }

  if (screenWidth >= 1610) {
    if (document.getElementById("post-3") !== null) {
      document.getElementById("post-3").style.display = "inline";
    }

    if (document.getElementById("post-4") !== null) {
      document.getElementById("post-4").style.display = "inline";
    }

    if (document.getElementById("post-5") !== null) {
      document.getElementById("post-5").style.display = "inline";
    }

    if (document.getElementById("post-6") !== null) {
      document.getElementById("post-6").style.display = "inline";
    }
  }

  if (screenWidth >= 1196) {
    if (document.getElementById("post-3") !== null) {
      document.getElementById("post-3").style.display = "inline";
    }

    if (document.getElementById("post-4") !== null) {
      document.getElementById("post-4").style.display = "inline";
    }
  }

  if (screenWidth >= 850) {
    if (document.getElementById("events-calendar-calendar") !== null) {
      var element = document.getElementById("events-calendar-calendar");
      element.style.display = "block";
      element.style.height = "440px";
    }
  }
  if (screenWidth < 850) {
    var element = document.getElementById("events-calendar-calendar");
    element.style.height = "0px";
  }
}

/* Attach the event listener to the window resize event*/
window.addEventListener("resize", handleScreenWidthChange);

/**  Call the function once initially to get the current screen width */
handleScreenWidthChange();

/** Callendar hiding functionality */
function toggleCalendarVisibility(event) {
  // Prevent default behavior of the <a> element click event
  event.preventDefault();
  var element = document.getElementById("events-calendar-calendar");
  if (element.style.height === "0px" || element.style.height === "") {
    element.style.height = "399px";
  } else {
    element.style.height = "0px";
    element.style.marginBottom = "0px";
  }
}

if (document.getElementById("calendar-button-id") !== null) {
  var toggleButton = document.getElementById("calendar-button-id");
  toggleButton.addEventListener("click", toggleCalendarVisibility);
}

/** Pagination */

// Number of posts to show per page
var postsPerPage = 12;

// Current page number
var currentPage = 1;

function showPosts() {
  var posts = document.getElementsByClassName("post");
  var startIndex = (currentPage - 1) * postsPerPage;
  var endIndex = startIndex + postsPerPage;

  for (var i = 0; i < posts.length; i++) {
    if (i >= startIndex && i < endIndex) {
      posts[i].style.display = "block";
    } else {
      posts[i].style.display = "none";
    }
  }
}

function updatePagination() {
  var posts = document.getElementsByClassName("post");
  var totalPages = Math.ceil(posts.length / postsPerPage);
  var pagesDiv = document.getElementById("pages");
  var paginationHTML = "";

  // return if cannot find pages block
  if (pagesDiv === null) {
    return;
  }

  if (totalPages <= 1) {
    pagesDiv.innerHTML = paginationHTML;
    return;
  }

  var adjacentButtons = 2;
  var maxButtons = adjacentButtons * 2 + 1;
  var isFirstPage = currentPage === 1;
  var isLastPage = currentPage === totalPages;

  paginationHTML +=
    "<button aria-label='pasukutinis puslapis' class='previous-btn button2' onclick='previousPage()' " +
    (isFirstPage ? "disabled" : "") +
    ">.</button>";

  if (totalPages <= maxButtons) {
    for (var i = 1; i <= totalPages; i++) {
      if (i === currentPage) {
        paginationHTML +=
          "<button aria-label='puslapių pasirinkimas' class='current-page button2' disabled>" +
          i +
          "</button>";
      } else {
        paginationHTML +=
          "<button aria-label='puslapių pasirinkimas' class='button2' onclick='goToPage(" +
          i +
          ")'>" +
          i +
          "</button>";
      }
    }
  } else {
    if (currentPage <= adjacentButtons + 1) {
      for (var i = 1; i <= maxButtons; i++) {
        if (i === currentPage) {
          paginationHTML +=
            "<button aria-label='puslapių pasirinkimas' class='current-page button2' disabled>" +
            i +
            "</button>";
        } else {
          paginationHTML +=
            "<button aria-label='puslapių pasirinkimas' class='button2' onclick='goToPage(" +
            i +
            ")'>" +
            i +
            "</button>";
        }
      }
      if (currentPage !== maxButtons) {
        paginationHTML +=
          "<button aria-label='puslapių pasirinkimas' class='button2' disabled>...</button>";
      }
      paginationHTML +=
        "<button aria-label='puslapių pasirinkimas' class='button2' onclick='goToPage(" +
        totalPages +
        ")'>" +
        totalPages +
        "</button>";
    } else if (currentPage >= totalPages - adjacentButtons) {
      paginationHTML +=
        "<button aria-label='puslapių pasirinkimas' class='button2' onclick='goToPage(1)'>1</button>";
      if (currentPage !== totalPages - adjacentButtons) {
        paginationHTML +=
          "<button aria-label='puslapių pasirinkimas' class='button2' disabled>...</button>";
      }
      for (var i = totalPages - maxButtons + 1; i <= totalPages; i++) {
        if (i === currentPage) {
          paginationHTML +=
            "<button aria-label='puslapių pasirinkimas' class='current-page button2' disabled>" +
            i +
            "</button>";
        } else {
          paginationHTML +=
            "<button aria-label='puslapių pasirinkimas' class='button2' onclick='goToPage(" +
            i +
            ")'>" +
            i +
            "</button>";
        }
      }
    } else {
      paginationHTML +=
        "<button aria-label='puslapių pasirinkimas' class='button2' onclick='goToPage(1)'>1</button>";
      if (currentPage !== adjacentButtons + 2) {
        paginationHTML +=
          "<button aria-label='puslapių pasirinkimas' class='button2' disabled>...</button>";
      }
      for (
        var i = currentPage - adjacentButtons;
        i <= currentPage + adjacentButtons;
        i++
      ) {
        if (i === currentPage) {
          paginationHTML +=
            "<button aria-label='puslapių pasirinkimas' class='current-page button2' disabled>" +
            i +
            "</button>";
        } else {
          paginationHTML +=
            "<button aria-label='puslapių pasirinkimas' class='button2' onclick='goToPage(" +
            i +
            ")'>" +
            i +
            "</button>";
        }
      }
      if (currentPage !== totalPages - adjacentButtons - 1) {
        paginationHTML +=
          "<button aria-label='puslapių pasirinkimas' class='button2' disabled>...</button>";
      }
      paginationHTML +=
        "<button aria-label='puslapių pasirinkimas' class='button2' onclick='goToPage(" +
        totalPages +
        ")'>" +
        totalPages +
        "</button>";
    }
  }

  paginationHTML +=
    "<button aria-label='sekantis puslapis' class='next-btn button2' onclick='nextPage()' " +
    (isLastPage ? "disabled" : "") +
    ">.</button>";

  pagesDiv.innerHTML = paginationHTML;
}

function goToPage(page) {
  currentPage = page;
  showPosts();
  updatePagination();
}

function previousPage() {
  if (currentPage > 1) {
    currentPage--;
    showPosts();
    updatePagination();
  }
}

function nextPage() {
  var posts = document.getElementsByClassName("post");
  var totalPages = Math.ceil(posts.length / postsPerPage);

  if (currentPage < totalPages) {
    currentPage++;
    showPosts();
    updatePagination();
  }
}

// Set initial visibility of posts
function setInitialVisibility() {
  var posts = document.getElementsByClassName("post");

  for (var i = 0; i < posts.length; i++) {
    if (i >= postsPerPage) {
      posts[i].style.display = "none";
    }
  }
}

// Show the initial page and pagination
function pagination() {
  // update page number
  currentPage = 1;
  setInitialVisibility();
  showPosts();
  updatePagination();
}

//Adding (.ics ) Event to personal calendar
function addEventToCalendar() {
  var eventNameCalendar = document
    .getElementById("id-post-title")
    .textContent.trim();

  // check if location exists
  if (document.getElementById("id-event-location")) {
    var eventLocationCalendar = document
      .getElementById("id-event-location")
      .textContent.trim();
  } else {
    var eventLocationCalendar = "";
  }

  var eventDateCalendar = document
    .getElementById("id-calendar-date")
    .textContent.trim();

  const eventName = eventNameCalendar;
  const eventLocation = eventLocationCalendar;
  const startDate = eventDateCalendar;

  function formatDateTime(date) {
    const year = date.getUTCFullYear();
    const month = pad(date.getUTCMonth() + 1);
    const day = pad(date.getUTCDate());
    const hour = pad(date.getUTCHours());
    const minute = pad(date.getUTCMinutes());
    const second = pad(date.getUTCSeconds());
    return `${year}${month}${day}T${hour}${minute}${second}Z`;
  }

  function pad(i) {
    return i < 10 ? `0${i}` : `${i}`;
  }

  const formtedDate = formatDateTime(new Date(startDate));

  const icsContent = `
  BEGIN:VCALENDAR\n
  VERSION:2.0\n
  BEGIN:VEVENT\n
  DTSTART:${formtedDate}\n
  SUMMARY:${eventName}\n
  LOCATION:${eventLocation}\n
  END:VEVENT\n
  END:VCALENDAR`;

  const blob = new Blob([icsContent], { type: "text/calendar;charset=utf-8" });
  const url = window.URL.createObjectURL(blob);
  const link = document.createElement("a");
  link.href = url;
  link.download = "event.ics";
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}
