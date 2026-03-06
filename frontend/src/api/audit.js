import api from './axios'

export const getAuditEvents = (projectId, page = 1) => api.get(`/projects/${projectId}/audit?page=${page}`)