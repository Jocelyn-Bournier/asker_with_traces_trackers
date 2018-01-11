<?php

namespace SimpleIT\ClaireExerciseBundle\Migrations\pdo_ibm;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/08/27 02:59:43
 */
class Version20140827145933 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claire_exercise_answer (
                id INTEGER GENERATED BY DEFAULT AS IDENTITY NOT NULL, 
                item_id INTEGER DEFAULT NULL, 
                attempt_id INTEGER DEFAULT NULL, 
                content CLOB(1M) NOT NULL, 
                mark DOUBLE PRECISION DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_D0B3344126F525E ON claire_exercise_answer (item_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_D0B3344B191BE6B ON claire_exercise_answer (attempt_id)
        ");
        $this->addSql("
            CREATE TABLE claire_exercise_attempt (
                id INTEGER GENERATED BY DEFAULT AS IDENTITY NOT NULL, 
                exercise_id INTEGER DEFAULT NULL, 
                user_id INTEGER DEFAULT NULL, 
                test_attempt_id INTEGER DEFAULT NULL, 
                created_at TIMESTAMP(0) NOT NULL, 
                \"position\" INTEGER DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_228E85D1E934951A ON claire_exercise_attempt (exercise_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_228E85D1A76ED395 ON claire_exercise_attempt (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_228E85D1CAA20852 ON claire_exercise_attempt (test_attempt_id)
        ");
        $this->addSql("
            CREATE TABLE claire_exercise_item (
                id INTEGER GENERATED BY DEFAULT AS IDENTITY NOT NULL, 
                exercise_id INTEGER DEFAULT NULL, 
                \"type\" VARCHAR(255) NOT NULL, 
                content CLOB(1M) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_F5D1234E934951A ON claire_exercise_item (exercise_id)
        ");
        $this->addSql("
            CREATE TABLE claire_exercise_stored_exercise (
                id INTEGER GENERATED BY DEFAULT AS IDENTITY NOT NULL, 
                exercise_model_id INTEGER DEFAULT NULL, 
                content CLOB(1M) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_7270807A7F19170F ON claire_exercise_stored_exercise (exercise_model_id)
        ");
        $this->addSql("
            CREATE TABLE claire_exercise_knowledge (
                id INTEGER GENERATED BY DEFAULT AS IDENTITY NOT NULL, 
                parent_id INTEGER DEFAULT NULL, 
                fork_from_id INTEGER DEFAULT NULL, 
                author_id INTEGER NOT NULL, 
                owner_id INTEGER NOT NULL, 
                \"type\" VARCHAR(255) NOT NULL, 
                title VARCHAR(255) NOT NULL, 
                content CLOB(1M) DEFAULT NULL, 
                draft SMALLINT NOT NULL, 
                complete SMALLINT NOT NULL, 
                complete_error VARCHAR(255) DEFAULT NULL, 
                \"public\" SMALLINT DEFAULT NULL, 
                archived SMALLINT NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_465F3A83727ACA70 ON claire_exercise_knowledge (parent_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_465F3A833AB8C0BA ON claire_exercise_knowledge (fork_from_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_465F3A83F675F31B ON claire_exercise_knowledge (author_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_465F3A837E3C61F9 ON claire_exercise_knowledge (owner_id)
        ");
        $this->addSql("
            CREATE TABLE claire_exercise_knowledge_knowledge_requirement (
                knowledge_id INTEGER NOT NULL, 
                required_id INTEGER NOT NULL, 
                PRIMARY KEY(knowledge_id, required_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_67A0678CE7DC6902 ON claire_exercise_knowledge_knowledge_requirement (knowledge_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_67A0678CDD3DFC3F ON claire_exercise_knowledge_knowledge_requirement (required_id)
        ");
        $this->addSql("
            CREATE TABLE claire_exercise_knowledge_metadata (
                meta_key VARCHAR(255) NOT NULL, 
                knowledge_id INTEGER NOT NULL, 
                meta_value VARCHAR(255) NOT NULL, 
                PRIMARY KEY(meta_key, knowledge_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_269D7264E7DC6902 ON claire_exercise_knowledge_metadata (knowledge_id)
        ");
        $this->addSql("
            CREATE TABLE claire_exercise_model (
                id INTEGER GENERATED BY DEFAULT AS IDENTITY NOT NULL, 
                resourcenode_id INTEGER DEFAULT NULL, 
                parent_id INTEGER DEFAULT NULL, 
                fork_from_id INTEGER DEFAULT NULL, 
                author_id INTEGER NOT NULL, 
                owner_id INTEGER NOT NULL, 
                \"type\" VARCHAR(255) NOT NULL, 
                title VARCHAR(255) NOT NULL, 
                content CLOB(1M) DEFAULT NULL, 
                draft SMALLINT NOT NULL, 
                complete SMALLINT NOT NULL, 
                complete_error VARCHAR(255) DEFAULT NULL, 
                \"public\" SMALLINT NOT NULL, 
                archived SMALLINT DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_C3EFD3877C292AE ON claire_exercise_model (resourcenode_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_C3EFD38727ACA70 ON claire_exercise_model (parent_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_C3EFD383AB8C0BA ON claire_exercise_model (fork_from_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_C3EFD38F675F31B ON claire_exercise_model (author_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_C3EFD387E3C61F9 ON claire_exercise_model (owner_id)
        ");
        $this->addSql("
            CREATE TABLE claire_exercise_model_resource_requirement (
                model_id INTEGER NOT NULL, 
                required_resource_id INTEGER NOT NULL, 
                PRIMARY KEY(model_id, required_resource_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_34BBE44E7975B7E7 ON claire_exercise_model_resource_requirement (model_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_34BBE44EC971F1B5 ON claire_exercise_model_resource_requirement (required_resource_id)
        ");
        $this->addSql("
            CREATE TABLE claire_exercise_model_knowledge_requirement (
                model_id INTEGER NOT NULL, 
                required_knowledge_id INTEGER NOT NULL, 
                PRIMARY KEY(model_id, required_knowledge_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_5CD51F8C7975B7E7 ON claire_exercise_model_knowledge_requirement (model_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_5CD51F8C1793B92A ON claire_exercise_model_knowledge_requirement (required_knowledge_id)
        ");
        $this->addSql("
            CREATE TABLE claire_exercise_model_metadata (
                meta_key VARCHAR(255) NOT NULL, 
                exercise_model_id INTEGER NOT NULL, 
                meta_value VARCHAR(255) NOT NULL, 
                PRIMARY KEY(meta_key, exercise_model_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_1FCD0C517F19170F ON claire_exercise_model_metadata (exercise_model_id)
        ");
        $this->addSql("
            CREATE TABLE claire_exercise_resource (
                id INTEGER GENERATED BY DEFAULT AS IDENTITY NOT NULL, 
                parent_id INTEGER DEFAULT NULL, 
                fork_from_id INTEGER DEFAULT NULL, 
                author_id INTEGER NOT NULL, 
                owner_id INTEGER NOT NULL, 
                \"type\" VARCHAR(255) NOT NULL, 
                title VARCHAR(255) NOT NULL, 
                content CLOB(1M) DEFAULT NULL, 
                draft SMALLINT NOT NULL, 
                complete SMALLINT NOT NULL, 
                complete_error VARCHAR(255) DEFAULT NULL, 
                \"public\" SMALLINT NOT NULL, 
                archived SMALLINT NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_E9AEB0BE727ACA70 ON claire_exercise_resource (parent_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_E9AEB0BE3AB8C0BA ON claire_exercise_resource (fork_from_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_E9AEB0BEF675F31B ON claire_exercise_resource (author_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_E9AEB0BE7E3C61F9 ON claire_exercise_resource (owner_id)
        ");
        $this->addSql("
            CREATE TABLE claire_exercise_resource_knowledge_requirement (
                resource_id INTEGER NOT NULL, 
                required_knowledge_id INTEGER NOT NULL, 
                PRIMARY KEY(
                    resource_id, required_knowledge_id
                )
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_79D9E3BE89329D25 ON claire_exercise_resource_knowledge_requirement (resource_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_79D9E3BE1793B92A ON claire_exercise_resource_knowledge_requirement (required_knowledge_id)
        ");
        $this->addSql("
            CREATE TABLE claire_exercise_resource_resource_requirement (
                resource_id INTEGER NOT NULL, 
                required_id INTEGER NOT NULL, 
                PRIMARY KEY(resource_id, required_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_579576FC89329D25 ON claire_exercise_resource_resource_requirement (resource_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_579576FCDD3DFC3F ON claire_exercise_resource_resource_requirement (required_id)
        ");
        $this->addSql("
            CREATE TABLE claire_exercise_resource_metadata (
                meta_key VARCHAR(255) NOT NULL, 
                resource_id INTEGER NOT NULL, 
                meta_value VARCHAR(255) NOT NULL, 
                PRIMARY KEY(meta_key, resource_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_115B5EA589329D25 ON claire_exercise_resource_metadata (resource_id)
        ");
        $this->addSql("
            CREATE TABLE claire_exercise_test (
                id INTEGER GENERATED BY DEFAULT AS IDENTITY NOT NULL, 
                test_model_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_C8394926EC16BCB1 ON claire_exercise_test (test_model_id)
        ");
        $this->addSql("
            CREATE TABLE claire_exercise_test_attempt (
                id INTEGER GENERATED BY DEFAULT AS IDENTITY NOT NULL, 
                test_id INTEGER DEFAULT NULL, 
                user_id INTEGER DEFAULT NULL, 
                created_at TIMESTAMP(0) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_783E4D1F1E5D0459 ON claire_exercise_test_attempt (test_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_783E4D1FA76ED395 ON claire_exercise_test_attempt (user_id)
        ");
        $this->addSql("
            CREATE TABLE claire_exercise_test_model (
                id INTEGER GENERATED BY DEFAULT AS IDENTITY NOT NULL, 
                author_id INTEGER DEFAULT NULL, 
                title VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_CB243285F675F31B ON claire_exercise_test_model (author_id)
        ");
        $this->addSql("
            CREATE TABLE claire_exercise_test_model_position (
                test_model_id INTEGER NOT NULL, 
                exercise_model_id INTEGER NOT NULL, 
                \"position\" INTEGER NOT NULL, 
                PRIMARY KEY(
                    test_model_id, exercise_model_id
                )
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_C31B436DEC16BCB1 ON claire_exercise_test_model_position (test_model_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_C31B436D7F19170F ON claire_exercise_test_model_position (exercise_model_id)
        ");
        $this->addSql("
            CREATE TABLE claire_exercise_test_position (
                test_id INTEGER NOT NULL, 
                exercise_id INTEGER NOT NULL, 
                \"position\" INTEGER NOT NULL, 
                PRIMARY KEY(test_id, exercise_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_6F95FF221E5D0459 ON claire_exercise_test_position (test_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_6F95FF22E934951A ON claire_exercise_test_position (exercise_id)
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_answer 
            ADD CONSTRAINT FK_D0B3344126F525E FOREIGN KEY (item_id) 
            REFERENCES claire_exercise_item (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_answer 
            ADD CONSTRAINT FK_D0B3344B191BE6B FOREIGN KEY (attempt_id) 
            REFERENCES claire_exercise_attempt (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_attempt 
            ADD CONSTRAINT FK_228E85D1E934951A FOREIGN KEY (exercise_id) 
            REFERENCES claire_exercise_stored_exercise (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_attempt 
            ADD CONSTRAINT FK_228E85D1A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_attempt 
            ADD CONSTRAINT FK_228E85D1CAA20852 FOREIGN KEY (test_attempt_id) 
            REFERENCES claire_exercise_test_attempt (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_item 
            ADD CONSTRAINT FK_F5D1234E934951A FOREIGN KEY (exercise_id) 
            REFERENCES claire_exercise_stored_exercise (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_stored_exercise 
            ADD CONSTRAINT FK_7270807A7F19170F FOREIGN KEY (exercise_model_id) 
            REFERENCES claire_exercise_model (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_knowledge 
            ADD CONSTRAINT FK_465F3A83727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES claire_exercise_knowledge (id)
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_knowledge 
            ADD CONSTRAINT FK_465F3A833AB8C0BA FOREIGN KEY (fork_from_id) 
            REFERENCES claire_exercise_knowledge (id)
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_knowledge 
            ADD CONSTRAINT FK_465F3A83F675F31B FOREIGN KEY (author_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_knowledge 
            ADD CONSTRAINT FK_465F3A837E3C61F9 FOREIGN KEY (owner_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_knowledge_knowledge_requirement 
            ADD CONSTRAINT FK_67A0678CE7DC6902 FOREIGN KEY (knowledge_id) 
            REFERENCES claire_exercise_knowledge (id)
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_knowledge_knowledge_requirement 
            ADD CONSTRAINT FK_67A0678CDD3DFC3F FOREIGN KEY (required_id) 
            REFERENCES claire_exercise_knowledge (id)
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_knowledge_metadata 
            ADD CONSTRAINT FK_269D7264E7DC6902 FOREIGN KEY (knowledge_id) 
            REFERENCES claire_exercise_knowledge (id)
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_model 
            ADD CONSTRAINT FK_C3EFD3877C292AE FOREIGN KEY (resourcenode_id) 
            REFERENCES claro_resource_node (id)
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_model 
            ADD CONSTRAINT FK_C3EFD38727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES claire_exercise_model (id)
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_model 
            ADD CONSTRAINT FK_C3EFD383AB8C0BA FOREIGN KEY (fork_from_id) 
            REFERENCES claire_exercise_model (id)
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_model 
            ADD CONSTRAINT FK_C3EFD38F675F31B FOREIGN KEY (author_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_model 
            ADD CONSTRAINT FK_C3EFD387E3C61F9 FOREIGN KEY (owner_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_model_resource_requirement 
            ADD CONSTRAINT FK_34BBE44E7975B7E7 FOREIGN KEY (model_id) 
            REFERENCES claire_exercise_model (id)
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_model_resource_requirement 
            ADD CONSTRAINT FK_34BBE44EC971F1B5 FOREIGN KEY (required_resource_id) 
            REFERENCES claire_exercise_resource (id)
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_model_knowledge_requirement 
            ADD CONSTRAINT FK_5CD51F8C7975B7E7 FOREIGN KEY (model_id) 
            REFERENCES claire_exercise_model (id)
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_model_knowledge_requirement 
            ADD CONSTRAINT FK_5CD51F8C1793B92A FOREIGN KEY (required_knowledge_id) 
            REFERENCES claire_exercise_knowledge (id)
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_model_metadata 
            ADD CONSTRAINT FK_1FCD0C517F19170F FOREIGN KEY (exercise_model_id) 
            REFERENCES claire_exercise_model (id)
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_resource 
            ADD CONSTRAINT FK_E9AEB0BE727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES claire_exercise_resource (id)
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_resource 
            ADD CONSTRAINT FK_E9AEB0BE3AB8C0BA FOREIGN KEY (fork_from_id) 
            REFERENCES claire_exercise_resource (id)
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_resource 
            ADD CONSTRAINT FK_E9AEB0BEF675F31B FOREIGN KEY (author_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_resource 
            ADD CONSTRAINT FK_E9AEB0BE7E3C61F9 FOREIGN KEY (owner_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_resource_knowledge_requirement 
            ADD CONSTRAINT FK_79D9E3BE89329D25 FOREIGN KEY (resource_id) 
            REFERENCES claire_exercise_resource (id)
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_resource_knowledge_requirement 
            ADD CONSTRAINT FK_79D9E3BE1793B92A FOREIGN KEY (required_knowledge_id) 
            REFERENCES claire_exercise_knowledge (id)
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_resource_resource_requirement 
            ADD CONSTRAINT FK_579576FC89329D25 FOREIGN KEY (resource_id) 
            REFERENCES claire_exercise_resource (id)
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_resource_resource_requirement 
            ADD CONSTRAINT FK_579576FCDD3DFC3F FOREIGN KEY (required_id) 
            REFERENCES claire_exercise_resource (id)
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_resource_metadata 
            ADD CONSTRAINT FK_115B5EA589329D25 FOREIGN KEY (resource_id) 
            REFERENCES claire_exercise_resource (id)
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_test 
            ADD CONSTRAINT FK_C8394926EC16BCB1 FOREIGN KEY (test_model_id) 
            REFERENCES claire_exercise_test_model (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_test_attempt 
            ADD CONSTRAINT FK_783E4D1F1E5D0459 FOREIGN KEY (test_id) 
            REFERENCES claire_exercise_test (id)
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_test_attempt 
            ADD CONSTRAINT FK_783E4D1FA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_test_model 
            ADD CONSTRAINT FK_CB243285F675F31B FOREIGN KEY (author_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_test_model_position 
            ADD CONSTRAINT FK_C31B436DEC16BCB1 FOREIGN KEY (test_model_id) 
            REFERENCES claire_exercise_test_model (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_test_model_position 
            ADD CONSTRAINT FK_C31B436D7F19170F FOREIGN KEY (exercise_model_id) 
            REFERENCES claire_exercise_model (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_test_position 
            ADD CONSTRAINT FK_6F95FF221E5D0459 FOREIGN KEY (test_id) 
            REFERENCES claire_exercise_test (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_test_position 
            ADD CONSTRAINT FK_6F95FF22E934951A FOREIGN KEY (exercise_id) 
            REFERENCES claire_exercise_stored_exercise (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claire_exercise_answer 
            DROP FOREIGN KEY FK_D0B3344B191BE6B
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_answer 
            DROP FOREIGN KEY FK_D0B3344126F525E
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_attempt 
            DROP FOREIGN KEY FK_228E85D1E934951A
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_item 
            DROP FOREIGN KEY FK_F5D1234E934951A
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_test_position 
            DROP FOREIGN KEY FK_6F95FF22E934951A
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_knowledge 
            DROP FOREIGN KEY FK_465F3A83727ACA70
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_knowledge 
            DROP FOREIGN KEY FK_465F3A833AB8C0BA
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_knowledge_knowledge_requirement 
            DROP FOREIGN KEY FK_67A0678CE7DC6902
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_knowledge_knowledge_requirement 
            DROP FOREIGN KEY FK_67A0678CDD3DFC3F
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_knowledge_metadata 
            DROP FOREIGN KEY FK_269D7264E7DC6902
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_model_knowledge_requirement 
            DROP FOREIGN KEY FK_5CD51F8C1793B92A
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_resource_knowledge_requirement 
            DROP FOREIGN KEY FK_79D9E3BE1793B92A
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_stored_exercise 
            DROP FOREIGN KEY FK_7270807A7F19170F
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_model 
            DROP FOREIGN KEY FK_C3EFD38727ACA70
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_model 
            DROP FOREIGN KEY FK_C3EFD383AB8C0BA
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_model_resource_requirement 
            DROP FOREIGN KEY FK_34BBE44E7975B7E7
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_model_knowledge_requirement 
            DROP FOREIGN KEY FK_5CD51F8C7975B7E7
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_model_metadata 
            DROP FOREIGN KEY FK_1FCD0C517F19170F
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_test_model_position 
            DROP FOREIGN KEY FK_C31B436D7F19170F
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_model_resource_requirement 
            DROP FOREIGN KEY FK_34BBE44EC971F1B5
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_resource 
            DROP FOREIGN KEY FK_E9AEB0BE727ACA70
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_resource 
            DROP FOREIGN KEY FK_E9AEB0BE3AB8C0BA
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_resource_knowledge_requirement 
            DROP FOREIGN KEY FK_79D9E3BE89329D25
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_resource_resource_requirement 
            DROP FOREIGN KEY FK_579576FC89329D25
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_resource_resource_requirement 
            DROP FOREIGN KEY FK_579576FCDD3DFC3F
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_resource_metadata 
            DROP FOREIGN KEY FK_115B5EA589329D25
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_test_attempt 
            DROP FOREIGN KEY FK_783E4D1F1E5D0459
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_test_position 
            DROP FOREIGN KEY FK_6F95FF221E5D0459
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_attempt 
            DROP FOREIGN KEY FK_228E85D1CAA20852
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_test 
            DROP FOREIGN KEY FK_C8394926EC16BCB1
        ");
        $this->addSql("
            ALTER TABLE claire_exercise_test_model_position 
            DROP FOREIGN KEY FK_C31B436DEC16BCB1
        ");
        $this->addSql("
            DROP TABLE claire_exercise_answer
        ");
        $this->addSql("
            DROP TABLE claire_exercise_attempt
        ");
        $this->addSql("
            DROP TABLE claire_exercise_item
        ");
        $this->addSql("
            DROP TABLE claire_exercise_stored_exercise
        ");
        $this->addSql("
            DROP TABLE claire_exercise_knowledge
        ");
        $this->addSql("
            DROP TABLE claire_exercise_knowledge_knowledge_requirement
        ");
        $this->addSql("
            DROP TABLE claire_exercise_knowledge_metadata
        ");
        $this->addSql("
            DROP TABLE claire_exercise_model
        ");
        $this->addSql("
            DROP TABLE claire_exercise_model_resource_requirement
        ");
        $this->addSql("
            DROP TABLE claire_exercise_model_knowledge_requirement
        ");
        $this->addSql("
            DROP TABLE claire_exercise_model_metadata
        ");
        $this->addSql("
            DROP TABLE claire_exercise_resource
        ");
        $this->addSql("
            DROP TABLE claire_exercise_resource_knowledge_requirement
        ");
        $this->addSql("
            DROP TABLE claire_exercise_resource_resource_requirement
        ");
        $this->addSql("
            DROP TABLE claire_exercise_resource_metadata
        ");
        $this->addSql("
            DROP TABLE claire_exercise_test
        ");
        $this->addSql("
            DROP TABLE claire_exercise_test_attempt
        ");
        $this->addSql("
            DROP TABLE claire_exercise_test_model
        ");
        $this->addSql("
            DROP TABLE claire_exercise_test_model_position
        ");
        $this->addSql("
            DROP TABLE claire_exercise_test_position
        ");
    }
}