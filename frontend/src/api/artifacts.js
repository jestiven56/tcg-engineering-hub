import api from './axios'

export const getArtifacts = (projectId) => api.get(`/projects/${projectId}/artifacts`)
export const getArtifact = (projectId, artifactId) => api.get(`/projects/${projectId}/artifacts/${artifactId}`)
export const updateArtifact = (projectId, artifactId, data) => api.put(`/projects/${projectId}/artifacts/${artifactId}`, data)
export const updateArtifactStatus = (projectId, artifactId, status) =>
  api.patch(`/projects/${projectId}/artifacts/${artifactId}/status`, { status })