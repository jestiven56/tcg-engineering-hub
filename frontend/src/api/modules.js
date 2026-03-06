import api from './axios'

export const getModules = (projectId, page = 1) => api.get(`/projects/${projectId}/modules?page=${page}`)
export const getModule = (projectId, moduleId) => api.get(`/projects/${projectId}/modules/${moduleId}`)
export const createModule = (projectId, data) => api.post(`/projects/${projectId}/modules`, data)
export const updateModule = (projectId, moduleId, data) => api.put(`/projects/${projectId}/modules/${moduleId}`, data)
export const validateModule = (projectId, moduleId) => api.patch(`/projects/${projectId}/modules/${moduleId}/validate`)
export const deleteModule = (projectId, moduleId) => api.delete(`/projects/${projectId}/modules/${moduleId}`)