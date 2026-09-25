(() => {
    'use strict';
    const scopedProjects = (projects, codes, year) => {
        const selected = new Set(codes);
        const unique = new Map();
        for (const project of projects) {
            if (year && String(project.year) !== String(year)) continue;
            if (!project.codes.some(code => selected.has(code))) continue;
            if (!unique.has(project.id)) unique.set(project.id, project);
        }
        return [...unique.values()];
    };
    const summarize = projects => {
        const counts = new Map();
        for (const project of projects) {
            for (const sdg of new Set(project.sdgs)) {
                counts.set(sdg, (counts.get(sdg) || 0) + 1);
            }
        }
        const ranked = [...counts].map(([sdg, count]) => ({sdg, count}))
            .sort((a, b) => b.count - a.count || a.sdg - b.sdg);
        const dominant = ranked.filter(item => item.count === ranked[0]?.count).map(item => item.sdg);
        const topics = new Map();
        for (const project of projects.filter(row => row.sdgs.some(sdg => dominant.includes(sdg)))) {
            for (const topic of new Set(project.topics)) {
                const key = topic.trim().toLocaleLowerCase();
                if (!topics.has(key)) topics.set(key, {topic, projects: new Set()});
                topics.get(key).projects.add(project.id);
            }
        }
        return {
            ranked, dominant, total: projects.length,
            withSdg: projects.filter(project => project.sdgs.length).length,
            topics: [...topics.values()].map(row => ({topic: row.topic, count: row.projects.size}))
                .sort((a, b) => b.count - a.count || a.topic.localeCompare(b.topic))
        };
    };
    globalThis.PublicationResearch = {scopedProjects, summarize};
})();
