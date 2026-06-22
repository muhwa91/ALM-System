import axios from "axios"

const api = axios.create({
	baseURL: "http://localhost:8080/api",
	timeout: 5000,
	headers: {
		"Content-Type": "application/json",
	},
	withCredentials: true, // httpOnly 쿠키 자동 전송
})

// 응답 인터셉터: 인증 만료 시 로그인 페이지로 이동
api.interceptors.response.use(
	(res) => res,
	(err) => {
		const isAuthRoute = err.config?.url?.startsWith("/auth/")
		if (err.response?.status === 401 && !isAuthRoute) {
			if (window.location.pathname !== "/login") {
				window.location.href = "/login"
			}
		}
		return Promise.reject(err)
	}
)

export default api
