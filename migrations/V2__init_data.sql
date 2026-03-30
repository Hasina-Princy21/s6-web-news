BEGIN;

INSERT INTO categories (name)
VALUES
    ('Breaking News'),
    ('Diplomacy'),
    ('Humanitarian'),
    ('Security'),
    ('Sanctions'),
    ('Energy'),
    ('Cyber'),
    ('Economy'),
    ('Middle East'),
    ('Europe and Eurasia'),
    ('Africa'),
    ('Asia Pacific'),
    ('Americas'),
    ('Peace Process'),
    ('Displacement'),
    ('Fact Check')
ON CONFLICT (name) DO NOTHING;

INSERT INTO articles (header, url_slug, content, latitude, longitude, created_at, updated_at)
VALUES
    (
        'Iran regional tension monitoring and diplomatic contacts',
        'iran-regional-tension-monitoring-and-diplomatic-contacts',
        $$
        <h2>Situation overview</h2>
        <p>Regional tension around Iran remains high after multiple security incidents and rapid diplomatic calls among regional capitals.</p>
        <p>Monitoring desks are tracking military posture, commercial shipping routes, and backchannel diplomacy in parallel.</p>
        <ul>
          <li>Focus area: Gulf maritime traffic and air defense alerts</li>
          <li>Priority: prevent accidental escalation</li>
        </ul>
        $$,
        35.689200,
        51.389000,
        NOW() - INTERVAL '19 days',
        NOW() - INTERVAL '19 days'
    ),
    (
        'Gaza ceasefire talks and aid convoy access update',
        'gaza-ceasefire-talks-and-aid-convoy-access-update',
        $$
        <h2>Negotiation track</h2>
        <p>Mediators continue ceasefire talks while agencies request safer and faster aid convoy movement.</p>
        <p>Local reporting highlights repeated disruptions near crossing points and pressure on health systems.</p>
        $$,
        31.500000,
        34.460000,
        NOW() - INTERVAL '18 days',
        NOW() - INTERVAL '18 days'
    ),
    (
        'Ukraine war front line pressure and energy grid risks',
        'ukraine-war-front-line-pressure-and-energy-grid-risks',
        $$
        <h2>Operational picture</h2>
        <p>Fighting intensity remains uneven across sectors while energy infrastructure risk drives humanitarian contingency planning.</p>
        <p>International partners continue air defense and repair support discussions.</p>
        $$,
        50.450000,
        30.523000,
        NOW() - INTERVAL '17 days',
        NOW() - INTERVAL '17 days'
    ),
    (
        'Sudan conflict humanitarian corridors and urban access',
        'sudan-conflict-humanitarian-corridors-and-urban-access',
        $$
        <h2>Access constraints</h2>
        <p>Relief organizations report severe movement constraints in urban zones and unstable corridor access windows.</p>
        <p>Water, food, and emergency medicine remain top priorities.</p>
        $$,
        15.500700,
        32.559900,
        NOW() - INTERVAL '16 days',
        NOW() - INTERVAL '16 days'
    ),
    (
        'Sahel insecurity and food supply chain disruption brief',
        'sahel-insecurity-and-food-supply-chain-disruption-brief',
        $$
        <h2>Regional brief</h2>
        <p>Road insecurity and seasonal pressure continue to disrupt food distribution in parts of the central Sahel.</p>
        <p>Coordination centers are scaling cross border logistics planning.</p>
        $$,
        12.650000,
        -8.000000,
        NOW() - INTERVAL '15 days',
        NOW() - INTERVAL '15 days'
    ),
    (
        'Yemen red sea shipping risk and port operations status',
        'yemen-red-sea-shipping-risk-and-port-operations-status',
        $$
        <h2>Maritime and port track</h2>
        <p>Shipping risk assessments remain elevated and insurers continue route reviews.</p>
        <p>Port operations are active but vulnerable to sudden disruption spikes.</p>
        $$,
        15.369400,
        44.191000,
        NOW() - INTERVAL '14 days',
        NOW() - INTERVAL '14 days'
    ),
    (
        'Syria cross border aid renewal and local ceasefire contacts',
        'syria-cross-border-aid-renewal-and-local-ceasefire-contacts',
        $$
        <h2>Humanitarian and political channels</h2>
        <p>Humanitarian actors seek predictable authorization windows while local intermediaries test de escalation contacts.</p>
        <p>Medical referral capacity remains limited in several districts.</p>
        $$,
        33.513800,
        36.276500,
        NOW() - INTERVAL '13 days',
        NOW() - INTERVAL '13 days'
    ),
    (
        'Myanmar conflict displacement trends near border crossings',
        'myanmar-conflict-displacement-trends-near-border-crossings',
        $$
        <h2>Population movement</h2>
        <p>Field teams report repeated displacement waves tied to local fighting and insecurity along transport corridors.</p>
        <p>Cross border reception planning remains under pressure.</p>
        $$,
        19.763300,
        96.078500,
        NOW() - INTERVAL '12 days',
        NOW() - INTERVAL '12 days'
    ),
    (
        'Eastern dr congo ceasefire mechanism and civilian safety',
        'eastern-dr-congo-ceasefire-mechanism-and-civilian-safety',
        $$
        <h2>Great Lakes focus</h2>
        <p>Observers note intermittent clashes despite local deconfliction mechanisms.</p>
        <p>Civilian protection and access to health services remain central concerns.</p>
        $$,
        -1.957900,
        30.112700,
        NOW() - INTERVAL '11 days',
        NOW() - INTERVAL '11 days'
    ),
    (
        'Haiti security environment and port access for relief goods',
        'haiti-security-environment-and-port-access-for-relief-goods',
        $$
        <h2>Urban security and logistics</h2>
        <p>Security volatility around key transport nodes is delaying relief shipments and market recovery.</p>
        <p>Agencies are prioritizing predictable access windows for essential goods.</p>
        $$,
        18.594400,
        -72.307400,
        NOW() - INTERVAL '10 days',
        NOW() - INTERVAL '10 days'
    ),
    (
        'Armenia azerbaijan border commission technical progress',
        'armenia-azerbaijan-border-commission-technical-progress',
        $$
        <h2>Diplomatic process</h2>
        <p>Technical teams continue border delimitation talks with periodic international facilitation.</p>
        <p>Confidence measures remain fragile but active.</p>
        $$,
        40.179200,
        44.499100,
        NOW() - INTERVAL '9 days',
        NOW() - INTERVAL '9 days'
    ),
    (
        'South china sea patrol activity and code of conduct talks',
        'south-china-sea-patrol-activity-and-code-of-conduct-talks',
        $$
        <h2>Maritime diplomacy</h2>
        <p>Competing patrol patterns continue while regional forums push for communication protocols at sea.</p>
        <p>Commercial traffic remains high with periodic warning notices.</p>
        $$,
        14.599500,
        120.984200,
        NOW() - INTERVAL '8 days',
        NOW() - INTERVAL '8 days'
    ),
    (
        'Afghanistan aid operations and banking channel constraints',
        'afghanistan-aid-operations-and-banking-channel-constraints',
        $$
        <h2>Aid delivery conditions</h2>
        <p>Organizations report recurring payment and liquidity bottlenecks impacting program continuity.</p>
        <p>Winterization and nutrition support remain priority tracks.</p>
        $$,
        34.555300,
        69.207500,
        NOW() - INTERVAL '7 days',
        NOW() - INTERVAL '7 days'
    ),
    (
        'Venezuela political talks and sanctions review timeline',
        'venezuela-political-talks-and-sanctions-review-timeline',
        $$
        <h2>Political dialogue</h2>
        <p>Negotiation channels between political actors remain active alongside external sanctions review discussions.</p>
        <p>Economic stabilization remains a central public concern.</p>
        $$,
        10.480600,
        -66.903600,
        NOW() - INTERVAL '6 days',
        NOW() - INTERVAL '6 days'
    ),
    (
        'Kosovo serbia dialogue facilitation and confidence steps',
        'kosovo-serbia-dialogue-facilitation-and-confidence-steps',
        $$
        <h2>European mediation</h2>
        <p>Facilitators are pushing incremental confidence steps after repeated security incidents.</p>
        <p>Municipal implementation details remain contested.</p>
        $$,
        42.662900,
        21.165500,
        NOW() - INTERVAL '5 days',
        NOW() - INTERVAL '5 days'
    ),
    (
        'Ethiopia reconstruction track and local service restoration',
        'ethiopia-reconstruction-track-and-local-service-restoration',
        $$
        <h2>Recovery track</h2>
        <p>Reconstruction planning is advancing unevenly across sectors, with focus on schools, clinics, and roads.</p>
        <p>Local service restoration remains critical for returns and stability.</p>
        $$,
        9.030000,
        38.740000,
        NOW() - INTERVAL '4 days',
        NOW() - INTERVAL '4 days'
    ),
    (
        'Niger regional cooperation on transit routes and border safety',
        'niger-regional-cooperation-on-transit-routes-and-border-safety',
        $$
        <h2>Cross border coordination</h2>
        <p>Regional actors are reviewing border procedures and transit route protection measures.</p>
        <p>Trade continuity and community safety are linked priorities.</p>
        $$,
        13.511600,
        2.125400,
        NOW() - INTERVAL '3 days',
        NOW() - INTERVAL '3 days'
    ),
    (
        'Iran nuclear file and iaea technical talks in vienna',
        'iran-nuclear-file-and-iaea-technical-talks-in-vienna',
        $$
        <h2>Non proliferation track</h2>
        <p>Technical meetings continue in Vienna on verification scope and sequencing of commitments.</p>
        <p>Diplomatic teams stress de escalation and structured communication.</p>
        $$,
        48.208200,
        16.373800,
        NOW() - INTERVAL '2 days',
        NOW() - INTERVAL '2 days'
    ),
    (
        'Israel lebanon blue line incidents and mediation efforts',
        'israel-lebanon-blue-line-incidents-and-mediation-efforts',
        $$
        <h2>Border risk management</h2>
        <p>Incident reporting around the Blue Line has increased, prompting urgent mediation contacts.</p>
        <p>Risk reduction messaging focuses on civilian protection and communication discipline.</p>
        $$,
        33.888600,
        35.495500,
        NOW() - INTERVAL '1 days',
        NOW() - INTERVAL '1 days'
    )
ON CONFLICT (url_slug) DO UPDATE
SET
    header = EXCLUDED.header,
    content = EXCLUDED.content,
    latitude = EXCLUDED.latitude,
    longitude = EXCLUDED.longitude,
    updated_at = NOW();

INSERT INTO article_categories (article_id, category_id)
SELECT a.id, c.id
FROM (
    VALUES
        ('iran-regional-tension-monitoring-and-diplomatic-contacts', 'Middle East'),
        ('iran-regional-tension-monitoring-and-diplomatic-contacts', 'Diplomacy'),
        ('iran-regional-tension-monitoring-and-diplomatic-contacts', 'Security'),
        ('gaza-ceasefire-talks-and-aid-convoy-access-update', 'Middle East'),
        ('gaza-ceasefire-talks-and-aid-convoy-access-update', 'Humanitarian'),
        ('gaza-ceasefire-talks-and-aid-convoy-access-update', 'Peace Process'),
        ('ukraine-war-front-line-pressure-and-energy-grid-risks', 'Europe and Eurasia'),
        ('ukraine-war-front-line-pressure-and-energy-grid-risks', 'Security'),
        ('ukraine-war-front-line-pressure-and-energy-grid-risks', 'Energy'),
        ('sudan-conflict-humanitarian-corridors-and-urban-access', 'Africa'),
        ('sudan-conflict-humanitarian-corridors-and-urban-access', 'Humanitarian'),
        ('sudan-conflict-humanitarian-corridors-and-urban-access', 'Displacement'),
        ('sahel-insecurity-and-food-supply-chain-disruption-brief', 'Africa'),
        ('sahel-insecurity-and-food-supply-chain-disruption-brief', 'Security'),
        ('sahel-insecurity-and-food-supply-chain-disruption-brief', 'Humanitarian'),
        ('yemen-red-sea-shipping-risk-and-port-operations-status', 'Middle East'),
        ('yemen-red-sea-shipping-risk-and-port-operations-status', 'Security'),
        ('yemen-red-sea-shipping-risk-and-port-operations-status', 'Energy'),
        ('syria-cross-border-aid-renewal-and-local-ceasefire-contacts', 'Middle East'),
        ('syria-cross-border-aid-renewal-and-local-ceasefire-contacts', 'Humanitarian'),
        ('syria-cross-border-aid-renewal-and-local-ceasefire-contacts', 'Peace Process'),
        ('myanmar-conflict-displacement-trends-near-border-crossings', 'Asia Pacific'),
        ('myanmar-conflict-displacement-trends-near-border-crossings', 'Displacement'),
        ('myanmar-conflict-displacement-trends-near-border-crossings', 'Humanitarian'),
        ('eastern-dr-congo-ceasefire-mechanism-and-civilian-safety', 'Africa'),
        ('eastern-dr-congo-ceasefire-mechanism-and-civilian-safety', 'Security'),
        ('eastern-dr-congo-ceasefire-mechanism-and-civilian-safety', 'Peace Process'),
        ('haiti-security-environment-and-port-access-for-relief-goods', 'Americas'),
        ('haiti-security-environment-and-port-access-for-relief-goods', 'Humanitarian'),
        ('haiti-security-environment-and-port-access-for-relief-goods', 'Security'),
        ('armenia-azerbaijan-border-commission-technical-progress', 'Europe and Eurasia'),
        ('armenia-azerbaijan-border-commission-technical-progress', 'Diplomacy'),
        ('armenia-azerbaijan-border-commission-technical-progress', 'Peace Process'),
        ('south-china-sea-patrol-activity-and-code-of-conduct-talks', 'Asia Pacific'),
        ('south-china-sea-patrol-activity-and-code-of-conduct-talks', 'Security'),
        ('south-china-sea-patrol-activity-and-code-of-conduct-talks', 'Diplomacy'),
        ('afghanistan-aid-operations-and-banking-channel-constraints', 'Asia Pacific'),
        ('afghanistan-aid-operations-and-banking-channel-constraints', 'Humanitarian'),
        ('afghanistan-aid-operations-and-banking-channel-constraints', 'Economy'),
        ('venezuela-political-talks-and-sanctions-review-timeline', 'Americas'),
        ('venezuela-political-talks-and-sanctions-review-timeline', 'Diplomacy'),
        ('venezuela-political-talks-and-sanctions-review-timeline', 'Sanctions'),
        ('kosovo-serbia-dialogue-facilitation-and-confidence-steps', 'Europe and Eurasia'),
        ('kosovo-serbia-dialogue-facilitation-and-confidence-steps', 'Peace Process'),
        ('kosovo-serbia-dialogue-facilitation-and-confidence-steps', 'Diplomacy'),
        ('ethiopia-reconstruction-track-and-local-service-restoration', 'Africa'),
        ('ethiopia-reconstruction-track-and-local-service-restoration', 'Humanitarian'),
        ('ethiopia-reconstruction-track-and-local-service-restoration', 'Economy'),
        ('niger-regional-cooperation-on-transit-routes-and-border-safety', 'Africa'),
        ('niger-regional-cooperation-on-transit-routes-and-border-safety', 'Security'),
        ('niger-regional-cooperation-on-transit-routes-and-border-safety', 'Diplomacy'),
        ('iran-nuclear-file-and-iaea-technical-talks-in-vienna', 'Middle East'),
        ('iran-nuclear-file-and-iaea-technical-talks-in-vienna', 'Diplomacy'),
        ('iran-nuclear-file-and-iaea-technical-talks-in-vienna', 'Sanctions'),
        ('israel-lebanon-blue-line-incidents-and-mediation-efforts', 'Middle East'),
        ('israel-lebanon-blue-line-incidents-and-mediation-efforts', 'Security'),
        ('israel-lebanon-blue-line-incidents-and-mediation-efforts', 'Peace Process')
) AS m(slug, category_name)
JOIN articles a ON a.url_slug = m.slug
JOIN categories c ON c.name = m.category_name
ON CONFLICT DO NOTHING;

COMMIT;
