import {test} from 'node:test';
import assert from 'node:assert/strict';
import '../../public/js/publication-research.js';
const {scopedProjects, summarize} = globalThis.PublicationResearch;
const projects = [
    {id:'P1',year:2026,codes:['D1','D2'],sdgs:[8,9],topics:['Digital business']},
    {id:'P2',year:2026,codes:['D1'],sdgs:[8],topics:['Digital business']},
    {id:'P3',year:2026,codes:['D3'],sdgs:[9],topics:['Computer vision']},
    {id:'P4',year:2026,codes:['D1'],sdgs:[],topics:['Unmapped project']},
    {id:'P5',year:2025,codes:['D1'],sdgs:[4],topics:['Education']}
];
test('SDGs count unique projects, not members, and follow year and faculty scope',()=>{
    const active=scopedProjects([...projects,projects[0]], ['D1','D2'], 2026);
    assert.equal(active.length,3);
    const result=summarize(active);
    assert.deepEqual(result.ranked,[{sdg:8,count:2},{sdg:9,count:1}]);
    assert.deepEqual(result.dominant,[8]);
    assert.equal(result.withSdg,2);
    assert.deepEqual(result.topics,[{topic:'Digital business',count:2}]);
    assert.equal(scopedProjects(projects,['D2'],2026).length,1);
    assert.deepEqual(summarize(scopedProjects(projects,['D1'],2025)).dominant,[4]);
});
test('All tied dominant SDGs are shown; empty filters cannot retain earlier results',()=>{
    assert.deepEqual(summarize(scopedProjects(projects,['D1','D2','D3'],2026)).dominant,[8,9]);
    assert.deepEqual(summarize(scopedProjects(projects,['unknown'],2026)).ranked,[]);
    assert.deepEqual(summarize(scopedProjects(projects,['D1'],2024)).topics,[]);
});
