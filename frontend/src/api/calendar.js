import calendarApi from "@/utils/calendarApi"

// 연/월별 공휴일 조회
export const getHolidays = (year, month) =>
    calendarApi.get("/getHoliDeInfo", {
        params: {
            solYear: year,
            solMonth: month.toString().padStart(2, "0"),
            _type: "json",
        },
    })