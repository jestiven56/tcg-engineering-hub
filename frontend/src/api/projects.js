import api from './axios'

export const getProjects = (page = 1) => api.get(`/projects?page=${page}`)
export const getProject = (id) => api.get(`/projects/${id}`)
export const createProject = (data) => api.post('/projects', data)
export const updateProject = (id, data) => api.put(`/projects/${id}`, data)
export const updateProjectStatus = (id, status) => api.patch(`/projects/${id}/status`, { status })
export const archiveProject = (id) => api.delete(`/projects/${id}`)