import axios from "axios"

const calendarApi = axios.create({
	baseURL: "https://apis.data.go.kr/B090041/openapi/service/SpcdeInfoService",
	timeout: 8000,
})

// 공공데이터포털 API는 serviceKey를 params로 넣으면 axios가 이중인코딩함
// → paramsSerializer로 직접 문자열 조합해서 방지
calendarApi.interceptors.request.use((config) => {
	const apiKey = import.meta.env.VITE_HOLIDAY_API_KEY
	const params = new URLSearchParams(config.params || {})
	params.set("serviceKey", apiKey)
	config.params = null
	config.url = `${config.url}?${params.toString()}`
	return config
})

calendarApi.interceptors.response.use(
	(response) => response.data,
	(error) => {
		console.error("달력 API 에러:", error.response || error.message)
		return Promise.reject(error)
	}
)

export default calendarApi
